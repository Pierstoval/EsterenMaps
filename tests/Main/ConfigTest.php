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

namespace Tests\Main;

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Repository\ZonesRepository;
use Main\Config;
use Main\Config\ConfigNormalizer;
use Main\Entity\ConfigItem;
use Main\Repository\ConfigItemRepository;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetWithEmptyResults(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(ConfigItemRepository::class);
        $config = $this->getConfig($em, $repo);

        $date = (new \DateTimeImmutable());
        $dateAsString = $date->format(Config::DATE_FORMAT);

        $repo->expects(static::once())->method('getAll')->with()->willReturn([]);
        $em->expects(static::once())
            ->method('persist')
            ->with(static::callback(
                static function ($arg) use ($dateAsString) {
                    return
                        \is_object($arg)
                        && $arg instanceof ConfigItem
                        && Config::HOLIDAY_START_DATE === $arg->getName()
                        && $dateAsString === $arg->getValue()
                    ;
                }
            ))
        ;
        $em->expects(static::once())
            ->method('transactional')
            ->willReturnCallback(static function (\Closure $transactionalClosure) use ($em) {
                $transactionalClosure($em);

                return true;
            })
        ;

        $value = $config->get(Config::HOLIDAY_START_DATE, $date);

        $configFetched = (\Closure::bind(function () { return $this->configFetched; }, $config, Config::class))();
        static::assertTrue($configFetched);
        static::assertSame($date, $value);
    }

    public function testGetWithSingleResult(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(ConfigItemRepository::class);
        $config = $this->getConfig($em, $repo);

        $date = new \DateTimeImmutable();

        $repo->expects(static::once())->method('getAll')->with()->willReturn([
            Config::HOLIDAY_START_DATE => $this->createItem(1, Config::HOLIDAY_START_DATE, $date->format(Config::DATE_FORMAT)),
        ]);

        $result = $config->get(Config::HOLIDAY_START_DATE);

        $configFetched = (\Closure::bind(function () { return $this->configFetched; }, $config, Config::class))();
        static::assertTrue($configFetched);
        static::assertSame($date->format(Config::DATE_FORMAT), $result->format(Config::DATE_FORMAT));
    }

    private function getConfig(?EntityManagerInterface $em = null, ?ConfigItemRepository $repo = null): Config
    {
        if (!$em) {
            $em = $this->createMock(EntityManagerInterface::class);
            $repo = $this->createMock(ConfigItemRepository::class);
        }

        $configNormalizer = new ConfigNormalizer($this->createMock(ZonesRepository::class));

        return new Config($em, $repo, $configNormalizer);
    }

    private function createItem(int $id, string $name, $value): ConfigItem
    {
        $item = ConfigItem::create($name, $value);

        \Closure::bind(function () use ($id): void {
            $this->id = $id;
        }, $item, ConfigItem::class)();

        return $item;
    }
}
