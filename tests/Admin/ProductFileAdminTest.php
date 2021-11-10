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

use Main\Entity\Product\ProductFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductFileAdminTest extends AbstractEasyAdminTest
{
    public const TEST_ID = '44981095-c02d-4856-b84f-66b857891138';

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
        return 'ProductFile';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return ProductFile::class;
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'title',
            'description',
            'fileName',
            'requiredPermission',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideNewFormData()
    {
        $file = $this->createFile();

        return [
            'data_to_submit' => [
                'title' => 'ProductFile test',
                'description' => 'Test description',
                'products' => [],
                'requiredPermission' => '',
                'file' => new UploadedFile($file, \basename($file)),
            ],
            'search_data' => [
                'title' => 'ProductFile test',
            ],
            'expected_data' => [
                'title' => 'ProductFile test',
                'description' => 'Test description',
                'products' => [],
                'requiredPermission' => '',
                'filePath' => self::TEST_ID.'_'.\basename($file).'.bin',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideEditFormData()
    {
        $file = $this->createFile();

        return [
            'data_to_submit' => [
                'id' => 1,
                'title' => 'ProductFile test',
                'description' => 'Test description',
                'products' => [],
                'requiredPermission' => '',
                'file' => new UploadedFile($file, \basename($file)),
            ],
            'search_data' => [
                'title' => 'ProductFile test',
            ],
            'expected_data' => [
                'id' => 1,
                'title' => 'ProductFile test',
                'description' => 'Test description',
                'products' => [],
                'requiredPermission' => '',
                'filePath' => self::TEST_ID.'_'.\basename($file).'.bin',
            ],
        ];
    }

    private function createFile(): string
    {
        $file = \tempnam(\sys_get_temp_dir(), 'product_file_test');

        static::assertFileExists($file);

        $result = \file_put_contents($file, 'Hello world!');

        static::assertSame(12, $result);

        $this->files[] = $file;

        return $file;
    }
}

namespace Main\Entity\Product;

function uuid_create(int $uuid_type = \UUID_TYPE_DEFAULT)
{
    return \Tests\Admin\ProductFileAdminTest::TEST_ID;
}
