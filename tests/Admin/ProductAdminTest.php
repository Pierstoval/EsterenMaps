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

namespace Tests\Admin;

use Main\Entity\Product\Product;

class ProductAdminTest extends AbstractEasyAdminTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityName(): string
    {
        return 'Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return Product::class;
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'title',
            'slug',
            'locale',
            'description',
            'category',
            'requiredPermission',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideNewFormData()
    {
        return [
            'data_to_submit' => [
                'title' => 'Product test',
                'locale' => 'fr',
                'description' => 'Test description',
                'requiredPermission' => '',
                'category' => '1',
            ],
            'search_data' => [
                'title' => 'Product test',
            ],
            'expected_data' => [
                'title' => 'Product test',
                'locale' => 'fr',
                'description' => 'Test description',
                'requiredPermission' => '',
                'category' => 1,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideEditFormData()
    {
        return [
            'data_to_submit' => [
                'id' => 1,
                'title' => 'Vermine test 2',
                'locale' => 'en',
                'description' => 'Vermine test description',
                'requiredPermission' => '',
                'category' => '1',
            ],
            'search_data' => [
                'title' => 'Vermine test 2',
            ],
            'expected_data' => [
                'title' => 'Vermine test 2',
                'locale' => 'en',
                'description' => 'Vermine test description',
                'requiredPermission' => '',
                'category' => 1,
            ],
        ];
    }
}
