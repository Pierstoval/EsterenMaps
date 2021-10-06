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

namespace EsterenMaps\Entity;

use EsterenMaps\DTO\Api\MapElementDTOInterface;
use User\Entity\User;

/**
 * This interface is used by the different "*ApiController" classes,
 * they have similar behavior and are used to handle the map editing process.
 * They can also be used for map user notes, hence the "isNoteFromUser" method.
 */
interface MapElementInterface
{
    public function isNoteFrom(): ?User;

    public function isNoteFromUser(User $user): bool;

    public static function fromApi(MapElementDTOInterface $dto): self;

    public function updateFromApi(MapElementDTOInterface $dto): void;
}
