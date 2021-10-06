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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="maps_routes_types")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\RoutesTypesRepository")
 */
class RouteType
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
     * @var Collection|Route[]
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\Route", mappedBy="routeType")
     */
    protected $routes;

    /**
     * @var Collection|TransportModifier[]
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\TransportModifier", mappedBy="routeType")
     */
    protected $transports;

    public function __construct()
    {
        $this->routes = new ArrayCollection();
        $this->transports = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function addRoute(Route $routes): void
    {
        $this->routes[] = $routes;
    }

    public function removeRoute(Route $routes): void
    {
        $this->routes->removeElement($routes);
    }

    /**
     * @return Collection&iterable<Route>
     */
    public function getRoutes(): iterable
    {
        return $this->routes;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function addTransport(TransportModifier $transports): void
    {
        $this->transports[] = $transports;
    }

    public function removeTransport(TransportModifier $transports): void
    {
        $this->transports->removeElement($transports);
    }

    /**
     * @return Collection&iterable<TransportModifier>
     */
    public function getTransports(): iterable
    {
        return $this->transports;
    }

    public function getTransport(TransportType $transportType): TransportModifier
    {
        $transports = $this->transports->filter(function (TransportModifier $element) use ($transportType) {
            return $element->getTransportType()->getId() === $transportType->getId();
        });

        if (!$transports->count()) {
            throw new \InvalidArgumentException('RouteType object should have all types of transports bound to it. Could not find: "'.$transportType.'".');
        }

        return $transports->first();
    }
}
