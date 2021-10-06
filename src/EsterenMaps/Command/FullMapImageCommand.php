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

use EsterenMaps\Repository\MapsRepository;
use EsterenMaps\Services\MapImageGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FullMapImageCommand extends Command
{
    protected static $defaultName = 'esterenmaps:map:full-image';

    private $mapImageManager;
    private $mapsRepository;

    public function __construct(MapsRepository $mapsRepository, MapImageGenerator $mapImageManager)
    {
        parent::__construct(static::$defaultName);
        $this->mapImageManager = $mapImageManager;
        $this->mapsRepository = $mapsRepository;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $id = $input->getArgument('map-id');

        $io->comment('Be careful: as maps may be huge, this application can use a lot of memory and take very long to execute.');

        $map = $this->mapsRepository->find($id);

        if (!$map) {
            throw new \RuntimeException(\sprintf('No map found with id "%s".', $id));
        }

        $outputFile = $input->getArgument('output');

        $ext = \pathinfo($outputFile, \PATHINFO_EXTENSION);
        if ('psd' !== $ext) {
            $outputFile .= '.psd';
        }

        if (\file_exists($outputFile)) {
            if (!$io->confirm("File \"{$outputFile}\" exists. Overwrite?")) {
                $io->comment('Well, okay, thanks anyway!');

                return 0;
            }

            \unlink($outputFile);
        }

        $io->comment('Generating map image for <info>"'.$map->getName().'"</info>');

        $indicator = new ProgressIndicator($io, null, 1, [
            '<fg=yellow>🌑</>',
            '<fg=yellow>🌒</>',
            '<fg=yellow>🌓</>',
            '<fg=yellow>🌔</>',
            '<fg=yellow>🌕</>',
            '<fg=yellow>🌖</>',
            '<fg=yellow>🌗</>',
            '<fg=yellow>🌘</>',
        ]);
        $indicator->start('Start process…');

        $this->mapImageManager->setProgressIndicator($indicator);
        $this->mapImageManager->generateImage($map, $outputFile);

        $io->success("Saved file to \"{$outputFile}\".");

        return 0;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a full jpg for a map with all its data.')
            ->addArgument('output', InputArgument::REQUIRED, 'The output file')
            ->addArgument('map-id', InputArgument::REQUIRED, 'Enter the id of the map you want to generate')
        ;
    }
}
