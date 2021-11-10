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

namespace EsterenMaps\DTO;

use Admin\DTO\EasyAdminDTOInterface;
use Admin\DTO\TranslatableDTOInterface;
use Admin\DTO\TranslatableDTOTrait;
use EsterenMaps\Entity\Faction;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FactionAdminDTO implements EasyAdminDTOInterface, TranslatableDTOInterface
{
    use TranslatableDTOTrait;

    /**
     * @var string[]
     *
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public ?array $translatedNames = [];

    /**
     * @var string[]
     *
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public ?array $translatedDescriptions = [];

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromEntity(object $entity, array $options = []): self
    {
        if (!$entity instanceof Faction) {
            throw new UnexpectedTypeException($entity, Faction::class);
        }

        $self = new self();

        $self->setTranslationsFromEntity($entity, $options);

        return $self;
    }

    public static function getTranslatableFields(): array
    {
        return [
            'name' => 'translatedNames',
            'description' => 'translatedDescriptions',
        ];
    }
}
