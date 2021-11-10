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

use EsterenMaps\Entity\MarkerType;

class MarkersTypesAdminTest extends AbstractEasyAdminTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return 'MarkersTypes';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return MarkerType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'name',
            'webIcon',
            'markers',
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
}
