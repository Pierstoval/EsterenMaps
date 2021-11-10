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

namespace EsterenMaps\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;

trait CanBeUserNote
{
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="is_note_from_user_id", nullable=true)
     */
    protected ?User $isNoteFrom = null;

    public function isNoteFrom(): ?User
    {
        return $this->isNoteFrom;
    }

    public function isNoteFromUser(User $user): bool
    {
        if (!$this->isNoteFrom) {
            return false;
        }

        return $user->getId() === $this->isNoteFrom->getId();
    }
}
