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
use EsterenMaps\DTO\FactionAdminDTO;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="maps_factions")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\FactionsRepository")
 */
class Faction
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     *
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    protected $description;

    /**
     * @Gedmo\Locale
     */
    private ?string $locale = null;

    public function __toString()
    {
        return $this->name;
    }

    public static function fromAdmin(FactionAdminDTO $dto): self
    {
        $self = new self();

        $self->locale = 'fr';
        $self->name = $dto->translatedNames['fr'];
        $self->description = $dto->translatedDescriptions['fr'];
        $self->book = $dto->book;

        return $self;
    }

    public function updateFromAdmin(FactionAdminDTO $dto): void
    {
        $this->locale = 'fr';
        $this->name = $dto->translatedNames['fr'];
        $this->description = $dto->translatedDescriptions['fr'];
        $this->book = $dto->book;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function getTranslatableLocale(): ?string
    {
        return $this->locale;
    }
}
