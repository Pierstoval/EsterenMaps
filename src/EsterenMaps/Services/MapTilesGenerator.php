<?php

declare(strict_types=1);

/*
 * This file is part of the Esteren Maps package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com> and Studio Agate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EsterenMaps\Services;

use EsterenMaps\Entity\Map;
use EsterenMaps\ImageManagement\ImageIdentification;
use Orbitale\Component\ImageMagick\Command;
use Symfony\Component\Filesystem\Filesystem;

class MapTilesGenerator
{
    private int $tileSize;
    private string $outputDirectory;
    private string $magickPath;

    private ?int $imgWidth = null;
    private ?int $imgHeight = null;

    /** @var ImageIdentification[][] */
    private array $identifications = [];

    public function __construct(int $tileSize, string $outputDirectory, string $imageMagickPath)
    {
        $this->tileSize = $tileSize;
        $this->outputDirectory = \rtrim($outputDirectory, '\\/');
        $this->magickPath = $imageMagickPath;
    }

    /**
     * Identifie les caractéristiques de la carte si ce n'est pas déjà fait
     * Renvoie l'identification demandée en fonction du zoom
     * Renvoie une exception si l'identification par ImageMagick ne fonctionne
     * pas.
     *
     * @throws \RunTimeException
     *
     * @return ImageIdentification|ImageIdentification[]
     */
    public function identifyImage(Map $map, string $imgFile, int $zoom = null)
    {
        if (!$this->imgWidth || !$this->imgHeight) {
            // Détermine la taille de l'image initiale une fois et place les attributs dans l'objet
            $size = \getimagesize($imgFile);

            if (!$size || !isset($size[0], $size[1])) {
                throw new \RuntimeException('Error while retrieving map dimensions');
            }

            [$this->imgWidth, $this->imgHeight] = $size;
        }

        if (!isset($this->identifications[$map->getId()][$zoom])) {
            // Calcul des ratios et du nombre maximum de vignettes
            $crop_unit = 2 ** ($map->getMaxZoom() - $zoom) * $this->tileSize;

            $max_tiles_x = \ceil($this->imgWidth / $crop_unit) - 1;
            $max_tiles_y = \ceil($this->imgHeight / $crop_unit) - 1;

            $max_width = $max_tiles_x * $this->tileSize;
            $max_height = $max_tiles_y * $this->tileSize;

            $max_width_global = $crop_unit * ($max_tiles_x + 1);
            $max_height_global = $crop_unit * ($max_tiles_y + 1);

            $this->identifications[$map->getId()][$zoom] = new ImageIdentification([
                'xmax' => $max_tiles_x,
                'ymax' => $max_tiles_y,
                'tiles_max' => $max_tiles_x * $max_tiles_y,
                'wmax' => $max_width,
                'hmax' => $max_height,
                'wmax_global' => $max_width_global,
                'hmax_global' => $max_height_global,
            ]);
        }

        if (null === $zoom) {
            return $this->identifications[$map->getId()];
        }

        return $this->identifications[$map->getId()][$zoom];
    }

    public function generateTiles(int $zoom, Map $map, string $pathPrefix, bool $debug = false): void
    {
        // This is a workaround to allow images to be stored with either global path or relative path
        if (!\file_exists($sourceImage = $map->getImage())) {
            $sourceImage = \rtrim($pathPrefix, '\\/').'/'.\ltrim($map->getImage(), '\\/');
        }

        if (!\file_exists($sourceImage)) {
            throw new \RuntimeException(\sprintf('Map image file "%s" cannot be found.', $sourceImage));
        }

        $max = $map->getMaxZoom();

        $ratio = 100 / (2 ** ($max - $zoom));

        $outputScheme = $this->outputDirectory.'/temp_tiles/'.$map->getId().'/'.$zoom.'.jpg';
        $outputFinal = $this->outputDirectory.'/'.$map->getId().'/'.$zoom.'/{x}/{y}.jpg';

        if (!\is_dir($outputDir = \dirname($outputScheme))) {
            (new Filesystem())->mkdir($outputDir);
        }

        // Supprime tout fichier existant
        $existingFiles = \glob(\dirname($outputScheme).'/*');
        foreach ($existingFiles as $file) {
            \unlink($file);
        }

        $this->identifyImage($map, $sourceImage);

        $w = $this->imgWidth;
        $h = $this->imgHeight;

        if ($w >= $h) {
            $h = $w;
        } else {
            $w = $h;
        }

        $cmd = (new Command($this->magickPath))
            ->convert($sourceImage)
            ->background('#000000')
            ->extent($w.'x'.$h)
            ->resize($ratio.'%')
            ->crop($this->tileSize.'x'.$this->tileSize)
            ->background('#000000')
            ->extent($this->tileSize.'x'.$this->tileSize)
            ->thumbnail($this->tileSize.'x'.$this->tileSize)
            ->output($outputScheme)
        ;

        $commandResponse = $cmd->run();

        if ($commandResponse->hasFailed()) {
            $error = \trim($commandResponse->getError());
            $msg = \trim('Error while processing conversion. Command returned error:'."\n\t".\str_replace("\n", "\n\t", $error));
            if ($debug) {
                $msg .= "\n".'Executed command : '."\n\t".$cmd->getCommand();
            }

            throw new \RuntimeException($msg);
        }

        $existingFiles = \glob(\dirname($outputScheme).'/*', \GLOB_NOSORT);

        \sort($existingFiles, \SORT_NATURAL | \SORT_FLAG_CASE);
        $existingFiles = \array_values($existingFiles);

        $modulo = \sqrt(\count($existingFiles));

        foreach ($existingFiles as $i => $file) {
            $x = \floor($i / $modulo);
            $y = $i % $modulo;
            $filename = \str_replace(['{x}', '{y}'], [$x, $y], $outputFinal);

            if (!\is_dir(\dirname($filename))
                && !\mkdir($concurrentDirectory = \dirname($filename), 0775, true)
                && !\is_dir($concurrentDirectory)
            ) {
                throw new \RuntimeException(\sprintf('Directory "%s" could not be created', $concurrentDirectory));
            }

            \rename($file, $filename);
        }

        // Supprime tout fichier existant
        $existingFiles = \glob(\dirname($outputScheme).'/*');
        foreach ($existingFiles as $file) {
            \unlink($file);
        }
    }
}
