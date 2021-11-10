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

use Subscription\Entity\Subscription;

class SubscriptionAdminTest extends AbstractEasyAdminTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return 'Subscriptions';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Subscription::class;
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'user',
            'type',
            'startsAt',
            'endsAt',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideNewFormData()
    {
        return [
            'data_to_submit' => [
                'user' => 1,
                'startsAt' => $start = \date('Y-m-d 00:00:00'),
                'endsAt' => $end = (new \DateTime('next month'))->format('Y-m-d 00:00:00'),
                'type' => 'subscription.esteren_maps',
            ],
            'search_data' => [
                'user' => 1,
                'type' => 'subscription.esteren_maps',
            ],
            'expected_data' => [
                'user' => 1,
                'startsAt' => $start,
                'endsAt' => $end,
                'type' => 'subscription.esteren_maps',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideEditFormData()
    {
        return false;
    }
}
