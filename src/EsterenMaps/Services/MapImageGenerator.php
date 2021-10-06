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

namespace EsterenMaps\Services;

use Doctrine\Common\Collections\Collection;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\Route;
use EsterenMaps\Model\LatLng;
use EsterenMaps\Model\MapBounds;
use EsterenMaps\Model\MapSize;
use EsterenMaps\Repository\MarkersRepository;
use EsterenMaps\Repository\RoutesRepository;
use EsterenMaps\Repository\ZonesRepository;
use Orbitale\Component\ImageMagick\Command;
use Orbitale\Component\ImageMagick\ReferenceClasses\Geometry;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Output\NullOutput;

final class MapImageGenerator
{
    private $publicDir;
    private $tmpDir;
    private $imageMagickPath;
    private $routesRepository;
    private $markersRepository;
    private $zonesRepository;

    /**
     * @var null|ProgressIndicator
     */
    private $progressIndicator;

    public function __construct(
        string $publicDir,
        string $projectDir,
        string $imageMagickPath,
        RoutesRepository $routesRepository,
        MarkersRepository $markersRepository,
        ZonesRepository $zonesRepository
    ) {
        $this->tmpDir = $projectDir.'/var/tmp/';

        if (!\is_dir($this->tmpDir) && !\mkdir($tmpDir = $this->tmpDir) && !\is_dir($tmpDir)) {
            throw new \RuntimeException(\sprintf('Temporary directory "%s" could not be created', $tmpDir));
        }

        $this->imageMagickPath = $imageMagickPath;
        $this->publicDir = $publicDir;

        $this->routesRepository = $routesRepository;
        $this->markersRepository = $markersRepository;
        $this->zonesRepository = $zonesRepository;
    }

    public function setProgressIndicator(?ProgressIndicator $progressIndicator): void
    {
        $this->progressIndicator = $progressIndicator;
    }

    /**
     * Generate an image file with :
     * - Map image as background,
     * - All routes, merged with background,
     * - All marker icons, merged with background,
     * - One layer for each marker name.
     */
    public function generateImage(Map $map, string $outputName): void
    {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Starting process…');
        $progress->advance();

        $ext = \pathinfo($outputName, \PATHINFO_EXTENSION);
        if ('psd' !== $ext) {
            $outputName .= '.psd';
        }

        $mapImage = \rtrim($this->publicDir, '\\/').'/'.\ltrim($map->getImage(), '/');

        // Reminder:
        // Bounds are two [lat,lng] arrays.
        // First one is south-west.
        // Second one is north-east.
        // [lat,lng] is similar to [y,x].
        // (yeah, it's reversed, this is how common maps work, and we're not common maps :D)
        $bounds = $map->getObjectBounds();

        $size = @\getimagesize($mapImage);

        if (empty($size) || (0 === $size[0]) || (0 === $size[1])) {
            throw new \RuntimeException(\sprintf('Could not find width nor height of image. Are you sure the "%s" image really exists?', $mapImage));
        }

        $mapSize = MapSize::create((int) $size[0], (int) $size[1]);

        // Create all layers that will end up in the PSD file in the end.
        $backgroundImage = $this->createBackgroundImage($mapImage, $mapSize);
        $routesImage = $this->createRoutesImage($this->routesRepository->findForMap($map), $mapSize, $bounds);
        $markersImage = $this->createMarkersImage($markers = $this->markersRepository->findForMap($map), $mapSize, $bounds);
        $markersTextImage = $this->createMarkersTextImage($markers, $mapSize, $bounds);

        $finalImage = $this->mergeImagesIntoPSD($backgroundImage, $routesImage, $markersImage, $markersTextImage);

        \unlink($backgroundImage);
        \unlink($routesImage);
        \unlink($markersImage);
        \unlink($markersTextImage);

        \rename($finalImage, $outputName);
    }

    public function createBackgroundImage(string $mapImage, MapSize $mapSize): string
    {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Creating background image');
        $progress->advance();

        $workingImagePath = $this->tmpDir.\uniqid('tmp_background_', true).'.png';

        $command = Command::create($this->imageMagickPath)
            ->convert($mapImage)
            ->background('#000000')
            ->extent($mapSize->getWidth().'x'.$mapSize->getHeight())
            ->thumbnail($mapSize->getWidth().'x'.$mapSize->getHeight())
            ->quality(100)
            ->output($workingImagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Command failed to create base image. Error::\n%s", $response->getError()));
        }

        $progress->advance();

        return $workingImagePath;
    }

    public function createRoutesImage(Collection $routes, MapSize $mapSize, MapBounds $bounds): string
    {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Creating routes image');
        $progress->advance();

        $routesImagePath = $this->tmpDir.\uniqid('tmp_routes_', true).'.png';

        $command = Command::create($this->imageMagickPath)
            ->newCommand('convert')
            ->size($mapSize->getWidth().'x'.$mapSize->getHeight())
            ->xc('none')
            ->output($routesImagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Command failed to create base routes image. Error::\n%s", $response->getError()));
        }

        $numberOfRoutes = \count($routes);
        $i = 0;
        foreach ($routes as $route) {
            $i++;
            $progress->setMessage(\sprintf(
                '[%d/%d] Add route <info>"%s"</info> ',
                $i,
                $numberOfRoutes,
                $route->getName()
            ));
            $progress->advance();
            $this->addRouteToImage($routesImagePath, $route, $mapSize, $bounds);
        }

        return $routesImagePath;
    }

    public function createMarkersImage(array $markers, MapSize $mapSize, MapBounds $bounds): string
    {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Creating markers image');
        $progress->advance();

        $markersImagePath = $this->tmpDir.\uniqid('tmp_markers_', true).'.png';

        $command = Command::create($this->imageMagickPath)
            ->newCommand('convert')
            ->size($mapSize->getWidth().'x'.$mapSize->getHeight())
            ->xc('none')
            ->output($markersImagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Command failed to create base markers image. Error::\n%s", $response->getError()));
        }

        $numberOfMarkers = \count($markers);
        $i = 0;
        foreach ($markers as $marker) {
            $i++;
            $progress->setMessage(\sprintf(
                '[%d/%d] Add marker <info>"%s"</info> ',
                $i,
                $numberOfMarkers,
                $marker->getName()
            ));
            $progress->advance();
            $this->addMarkerToImage($markersImagePath, $marker, $mapSize, $bounds);
        }

        return $markersImagePath;
    }

    public function createMarkersTextImage(array $markers, MapSize $mapSize, MapBounds $bounds): string
    {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Creating markers text image');
        $progress->advance();

        $markersImagePath = $this->tmpDir.\uniqid('tmp_markers_text_', true).'.png';

        $command = Command::create($this->imageMagickPath)
            ->newCommand('convert')
            ->size($mapSize->getWidth().'x'.$mapSize->getHeight())
            ->xc('none')
            ->output($markersImagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Command failed to create base markers image. Error::\n%s", $response->getError()));
        }

        $numberOfMarkers = \count($markers);
        $i = 0;
        foreach ($markers as $marker) {
            $i++;
            $progress->setMessage(\sprintf(
                '[%d/%d] Add text for marker <info>"%s"</info> ',
                $i,
                $numberOfMarkers,
                $marker->getName()
            ));
            $progress->advance();
            $this->addMarkerTextToImage($markersImagePath, $marker, $mapSize, $bounds);
        }

        return $markersImagePath;
    }

    private function addRouteToImage(string $imagePath, Route $route, MapSize $size, MapBounds $bounds): void
    {
        $polylineCoordinates = [];

        foreach ($route->getLatLngs() as $latlng) {
            $xy = $this->convertLatLngToXY($latlng, $size, $bounds);

            $polylineCoordinates[] = \number_format($xy['x'], 2, '.', '').','.\number_format($xy['y'], 2, '.', '');

            $this->getProgressIndicator()->advance();
        }

        $command = Command::create($this->imageMagickPath)
            ->mogrify()
            ->strokeWidth(5)
            ->polyline($polylineCoordinates, $route->getColor())
            ->output($imagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Failed to add route \"%s\" to the map. Error::\n%s", $route->getName(), $response->getError()));
        }
    }

    private function addMarkerToImage(string $imagePath, Marker $marker, MapSize $size, MapBounds $bounds): void
    {
        $xy = $this->convertLatLngToXY($marker->getLatLng(), $size, $bounds, $marker);

        $finalPathToWebIcon = $this->publicDir.$marker->getWebIcon();

        // Add marker icon
        $command = Command::create($this->imageMagickPath)
            ->composite()
            ->geometry(Geometry::createFromParameters(null, null, (int) $xy['x'], (int) $xy['y']))
            ->file($finalPathToWebIcon) // Icon to add to the image
            ->file($imagePath) // "composite" needs two images: first is the input
            ->output($imagePath) // second is the output.
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Failed to add marker \"%s\" to the map. Error::\n%s", $marker->getName(), $response->getError()));
        }
    }

    private function addMarkerTextToImage(string $imagePath, Marker $marker, MapSize $size, MapBounds $bounds): void
    {
        $xy = $this->convertLatLngToXY($marker->getLatLng(), $size, $bounds, $marker);

        // Add marker text
        // As we use "gravity center" to center the text,
        // the geometry must be withdrawed with width/2 and height/2 for the "cursor" to stay at {0,0},
        // And then we add final coordinates to the point as "offset".
        $x = (-$size->getWidth() / 2) + ((int) $xy['x']);
        $y = (-$size->getHeight() / 2) + ((int) $xy['y']) - ($marker->getIconHeight() / 2);

        $baseTextOptions = [
            'textSize' => 20,
            'text' => $marker->getName(),
            'geometry' => Geometry::createFromParameters(null, null, (int) $x, (int) $y),
            'textColor' => 'white',
            'strokeColor' => 'black',
            'strokeWidth' => 1,
        ];

        $command = @Command::create($this->imageMagickPath)
            ->mogrify()
            ->rawCommand('-gravity')->rawCommand('Center')
            ->font(\dirname(__DIR__, 3).'/assets/esteren/fonts/times.ttf')
            ->rawCommand('-kerning')->rawCommand('1')
            ->rawCommand('-weight')->rawCommand('700')
            ->text($baseTextOptions)
            ->text($baseTextOptions + ['strokeWidth' => 2, 'strokeColor' => 'white'])
            ->output($imagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Failed to add marker text \"%s\" to the map. Error::\n%s", $marker->getName(), $response->getError()));
        }
    }

    private function convertLatLngToXY(LatLng $latlng, MapSize $size, MapBounds $mapBounds, Marker $marker = null): array
    {
        $xRatio = $size->getWidth() / $mapBounds->getNorthEast()->getLng();
        $yRatio = $size->getHeight() / $mapBounds->getSouthWest()->getLat();

        $xOffset = 0;
        $yOffset = 0;

        /*
         * If we specify a marker, we withdraw its icon width and height,
         * in order to position the "x" and "y" cursor at the right top-left part,
         * mostly when positionning marker icons or text.
         */
        if ($marker) {
            $xOffset -= ($marker->getIconWidth() / 2);
            $yOffset -= ($marker->getIconHeight() / 2);
        }

        // Formula:
        // (coordinate * ratio) + offset

        return [
            'x' => ($latlng->getLng() * $xRatio) + $mapBounds->getNorthEast()->getLat() - $xOffset,
            'y' => ($latlng->getLat() * $yRatio) + $mapBounds->getSouthWest()->getLng() - $yOffset,
        ];
    }

    private function getProgressIndicator(): ProgressIndicator
    {
        if (!$this->progressIndicator) {
            // Fall back on indicator to null output.
            $this->progressIndicator = new ProgressIndicator(new NullOutput());
        }

        return $this->progressIndicator;
    }

    private function mergeImagesIntoPSD(
        string $backgroundImage,
        string $routesImage,
        string $markersImage,
        string $markersTextImage
    ): string {
        $progress = $this->getProgressIndicator();
        $progress->setMessage('Creating final PSD file');
        $progress->advance();

        $finalImagePath = $this->tmpDir.\uniqid('tmp_images_final_psd', true).'.psd';

        $command = Command::create($this->imageMagickPath)
            ->newCommand('convert')
            ->rawCommand('-label')->rawCommand('background')->file($backgroundImage)
            ->rawCommand('\(')
            ->rawCommand('-clone')->rawCommand('0') // Clone first index ( = first image in the script)
            ->rawCommand('-background')->rawCommand('none')
            ->rawCommand('-mosaic')
            ->rawCommand('\)')
            ->rawCommand('-insert')->rawCommand('0') // Insert previous index to the image sequence
            ->rawCommand('-label')->rawCommand('routes')->file($routesImage)
            ->rawCommand('-label')->rawCommand('markers')->file($markersImage)
            ->rawCommand('-label')->rawCommand('markersText')->file($markersTextImage)
            ->rawCommand('-set')->rawCommand('colorspace')->rawCommand('sRGB')
            ->output($finalImagePath)
        ;

        $response = $command->run();

        if ($response->hasFailed()) {
            throw new \RuntimeException(\sprintf("Command failed to create PSD file. Error::\n%s", $response->getError()));
        }

        $progress->advance();

        return $finalImagePath;
    }
}
