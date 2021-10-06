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

namespace EsterenMaps\DTO;

use Admin\DTO\EasyAdminDTOInterface;
use Admin\DTO\TranslatableDTOInterface;
use Admin\DTO\TranslatableDTOTrait;
use EsterenMaps\Entity\MarkerType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MarkerTypeAdminDTO implements EasyAdminDTOInterface, TranslatableDTOInterface
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

    /**
     * This property must be updated by the controller once the file is uploaded.
     */
    public ?string $uploadedFileName = null;

    /**
     * @Assert\GreaterThan(0)
     */
    public ?int $iconWidth = null;

    /**
     * @Assert\GreaterThan(0)
     */
    public ?int $iconHeight = null;

    public ?int $iconCenterX = null;

    public ?int $iconCenterY = null;

    /**
     * @Assert\Image(
     *     minWidth="1",
     *     minHeight="1",
     *     maxWidth="50",
     *     maxHeight="50",
     *     detectCorrupted=true
     * )
     */
    private ?UploadedFile $iconFile = null;

    private ?array $imageData = null;

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromEntity(object $entity, array $options = []): self
    {
        if (!$entity instanceof MarkerType) {
            throw new UnexpectedTypeException($entity, MarkerType::class);
        }

        $self = new self();

        $self->setTranslationsFromEntity($entity, $options);

        $self->iconWidth = $entity->getIconWidth();
        $self->iconHeight = $entity->getIconHeight();
        $self->iconCenterX = $entity->getIconCenterX();
        $self->iconCenterY = $entity->getIconCenterY();

        return $self;
    }

    public static function getTranslatableFields(): array
    {
        return [
            'name' => 'translatedNames',
            'description' => 'translatedDescriptions',
        ];
    }

    public function getUploadedFileName(): ?string
    {
        if (!$this->uploadedFileName) {
            return null;
        }

        return $this->uploadedFileName;
    }

    public function getIconFile(): ?UploadedFile
    {
        return $this->iconFile;
    }

    public function setIconFile(?UploadedFile $iconFile): void
    {
        $this->iconFile = $iconFile;

        if (!$iconFile) {
            return;
        }

        $data = \getimagesize($this->iconFile->getPathname());

        if (!$this->iconWidth) {
            $this->iconWidth = $data[0];
        }
        if (!$this->iconHeight) {
            $this->iconHeight = $data[1];
        }
        if (!$this->iconCenterX) {
            $this->iconCenterX = (int) ($data[0] / 2);
        }
        if (!$this->iconCenterY) {
            $this->iconCenterY = (int) ($data[1] / 2);
        }
    }
}
