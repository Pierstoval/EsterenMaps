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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="maps_zones_types")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\ZonesTypesRepository")
 */
class ZoneType
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=75, nullable=true)
     */
    protected $color;

    /**
     * @var null|ZoneType
     *
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\ZoneType")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $parent;

    /**
     * @var Collection|Zone[]
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\Zone", mappedBy="zoneType")
     */
    protected $zones;

    protected $children = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    public function __toString()
    {
        return ($this->parent ? '> ' : '').$this->id.' '.$this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addZone(Zone $zones): self
    {
        $this->zones[] = $zones;

        return $this;
    }

    public function removeZone(Zone $zones): void
    {
        $this->zones->removeElement($zones);
    }

    /**
     * @return Collection&iterable<Zone>
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * @param ZoneType $parent
     */
    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function addChild(self $child): void
    {
        $this->children[$child->getId()] = $child;
    }

    /**
     * @param ZoneType[] $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int|ZoneType $child
     */
    public function removeChild($child): self
    {
        if (!\is_object($child) && isset($this->children[$child])) {
            unset($this->children[$child]);
        } elseif (\is_object($child)) {
            unset($this->children[$child->getId()]);
        }

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
