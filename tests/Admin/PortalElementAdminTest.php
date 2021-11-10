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

namespace Tests\Admin;

use Agate\Entity\PortalElement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PortalElementAdminTest extends AbstractEasyAdminTest
{
    private const TEMPFILE_REGEX = '~^portal_element_[a-z0-9_-]+_pe_[a-z0-9]+\.[0-9]+\.jpe?g$~isUu';

    private array $files = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->files as $file) {
            if (null !== $file && \file_exists($file)) {
                \unlink($file);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName(): string
    {
        return 'PortalElement';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return PortalElement::class;
    }

    /**
     * @group functional
     */
    public function test new valid file upload(): void
    {
        $client = $this->getHttpClient();

        $class = PortalElement::class;

        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        $em
            ->createQuery(
                <<<DQL
                DELETE FROM {$class} element
                WHERE element.portal = :portal
                AND element.locale = :locale
                DQL
            )
            ->setParameter('portal', 'agate')
            ->setParameter('locale', 'en')
            ->execute()
        ;

        $file = $this->createImage();

        $submitted = [
            'portal' => 'agate',
            'locale' => 'en',
            'image' => new UploadedFile($file, 'uploaded_file.jpg'),
            'title' => 'Portail Esteren',
            'subtitle' => 'sub',
            'buttonText' => 'button',
            'buttonLink' => '/',
        ];

        $expected = [
            'portal' => 'agate',
            'locale' => 'en',
            'title' => 'Portail Esteren',
            'subtitle' => 'sub',
            'buttonText' => 'button',
            'buttonLink' => '/',
        ];

        $search = [
            'portal' => 'agate',
            'locale' => 'en',
        ];

        /** @var PortalElement $entity */
        $entity = $this->submitData($submitted, $expected, $search, 'new', $client);

        static::assertMatchesRegularExpression(self::TEMPFILE_REGEX, $entity->getImageUrl(), $entity->getImageUrl());

        $filePath = static::$kernel->getContainer()->getParameter('kernel.project_dir').'/public/uploads/portal/'.$entity->getImageUrl();

        static::assertFileExists($filePath);

        \unlink($filePath);
    }

    /**
     * @group functional
     */
    public function test edit valid portal element and old image is removed(): void
    {
        $submitted = [
            'image' => new UploadedFile($this->createImage(), 'uploaded_file.jpg'),
            'title' => 'Portail Esteren',
            'subtitle' => 'sub',
            'buttonText' => 'button',
            'buttonLink' => '/',
        ];

        $expected = [
            'id' => 1,
            'portal' => 'esteren',
            'locale' => 'fr',
            'title' => 'Portail Esteren',
            'subtitle' => 'sub',
            'buttonText' => 'button',
            'buttonLink' => '/',
        ];

        $search = [
            'portal' => 'esteren',
            'locale' => 'fr',
        ];

        $client = $this->getHttpClient();

        $uploadDir = static::$container->getParameter('kernel.project_dir').'/public/uploads/portal/';

        $fileToOverride = $this->createImage(10, 10, $uploadDir);
        static::assertFileExists($fileToOverride);
        $baseNameToOverride = \basename($fileToOverride);

        /** @var EntityManagerInterface $em */
        $em = static::$container->get('doctrine')->getManager();
        $metadata = $em->getClassMetadata(PortalElement::class);
        $tableName = $metadata->getTableName();
        $qb = $em->getConnection()->createQueryBuilder();
        $qb
            ->update($tableName)
            ->set($metadata->getColumnName('imageUrl'), $qb->createNamedParameter($baseNameToOverride))
            ->where($metadata->getColumnName('portal').' = '.$qb->createNamedParameter($search['portal']))
            ->andWhere($metadata->getColumnName('locale').' = '.$qb->createNamedParameter($search['locale']))
            ->execute()
        ;

        $entity = $em->getRepository(PortalElement::class)->findOneBy($search);

        static::assertInstanceOf(PortalElement::class, $entity);
        static::assertSame($entity->getImageUrl(), $baseNameToOverride);

        // Actual admin action
        $entity = $this->submitData($submitted, $expected, $search, 'edit', $client);

        static::assertMatchesRegularExpression(self::TEMPFILE_REGEX, $entity->getImageUrl(), $entity->getImageUrl());

        $filePath = $uploadDir.$entity->getImageUrl();

        static::assertFileExists($filePath);
        static::assertFileDoesNotExist($fileToOverride);

        \unlink($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'portal',
            'locale',
            'imageUrl',
            'title',
            'subtitle',
            'buttonText',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideNewFormData()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function provideEditFormData()
    {
        return false;
    }

    private function createImage(int $width = 1900, $height = 900, $dir = null): string
    {
        $file = \tempnam($dir ?: \sys_get_temp_dir(), 'portal_test');

        static::assertFileExists($file);

        \rename($file, $file.'.jpg');

        $file .= '.jpg';

        static::assertFileExists($file);

        $result = \imagejpeg(\imagecreate($width, $height), $file);

        static::assertTrue($result, \sprintf('"imagejpeg()" returned an error when creating file "%s".', $file));

        $this->files[] = $file;

        return $file;
    }
}
