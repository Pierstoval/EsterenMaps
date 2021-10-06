<?php

declare(strict_types=1);

/*
 * This file is part of the Agate Apps package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com> and Studio Agate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EsterenMaps\Command;

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Entity\Map;
use EsterenMaps\Repository\MapsRepository;
use EsterenMaps\Services\MapTilesGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MapTilesCommand extends Command
{
    protected static $defaultName = 'esterenmaps:map:generate-tiles';

    private $projectDir;
    private $em;
    private $tilesManager;

    public function __construct(string $projectDir, EntityManagerInterface $em, MapTilesGenerator $tilesManager)
    {
        parent::__construct(static::$defaultName);
        $this->projectDir = $projectDir;
        $this->em = $em;
        $this->tilesManager = $tilesManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate all tiles for a specific map.')
            ->setHelp('This command is used to generate a tile image for one of your maps.'."\n"
                .'You can specify the id of the map by adding it as an argument, or as an option with "-i x" or "--i=x" where "x" is the map id'."\n"
                ."\n".'The command will generate all tiles of a map. The tiles number is calculated upon the image size and the maxZoom value'
                ."\n".'The higher is the maxZoom value, higher is the number of tiles.'
                ."\n".'This command can take a long time to execute, depending of your system.'
                ."\n".'but do not worry : you can restart it at any time and skip all existing files')
            ->addArgument('id', InputArgument::OPTIONAL, 'Enter the id of the map you want to generate', null)
            ->addOption('replace', 'r', InputOption::VALUE_NONE, 'Replaces all existing tiles')
            ->addOption('skip', 'k', InputOption::VALUE_NONE, 'Skip all existing tiles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $id = $input->hasArgument('id') ? $input->getArgument('id') : null;

        /** @var MapsRepository $repo */
        $repo = $this->em->getRepository(Map::class);

        $list = null;

        /** @var Map $map */
        $map = null;

        do {
            // Finds a map
            $map = $repo->findOneBy(['id' => $id]);

            // If no map is found, we'll ask the user to choose between any of the maps in the database
            if (!$map) {
                $tmp = $repo->findAll();
                /** @var Map[] $maps_list */
                $maps_list = [];
                foreach ($tmp as $item) {
                    $maps_list[$item->getId()] = $item;
                }

                if (!\count($maps_list)) {
                    $io->error('There is no map in the database.');

                    return 1;
                }
                if (null !== $id) {
                    $io->warning('No map with id: '.$id);
                }
                $id = $io->choice('Select a map to generate:', $maps_list);
            }
        } while (!$map);

        $maxZoom = $map->getMaxZoom();
        $i = 0;

        /** @var ConsoleSectionOutput|OutputInterface $section */
        $section = $output;
        $overwriteMethod = 'writeln';

        if ($output instanceof ConsoleOutputInterface) {
            $section = $output->section();
            $section->writeln(' Generating map tiles for "'.$map->getName().'"');
            $overwriteMethod = 'overwrite';
        }

        try {
            $section->writeln('');
            do {
                $section->{$overwriteMethod}(' Processing extraction for zoom value '.$i);
                $this->tilesManager->generateTiles($i, $map, $this->projectDir.'/public/', true);
                $i++;
            } while ($i <= $maxZoom);
            $section->{$overwriteMethod}(' Processed '.$maxZoom.' zoom levels.');
        } catch (\Exception $e) {
            throw new \RuntimeException('Error while processing extraction for zoom value "'.((string) $i).'".', 1, $e);
        }

        $io->success('Done!');

        return 0;
    }
}
