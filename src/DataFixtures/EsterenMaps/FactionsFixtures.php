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

namespace DataFixtures\EsterenMaps;

use DateTime;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use EsterenMaps\Entity\Faction;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class FactionsFixtures extends ArrayFixture implements ORMFixtureInterface
{
    protected function getEntityClass(): string
    {
        return Faction::class;
    }

    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-factions-';
    }

    protected function getMethodNameForReference(): string
    {
        return 'getName';
    }

    protected function getObjects(): array
    {
        return [
            [
                'name' => 'Faction Test',
                'description' => 'This is just a test faction.',
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
        ];
    }
}
