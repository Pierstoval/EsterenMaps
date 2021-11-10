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

namespace EsterenMaps\Form;

use EsterenMaps\Entity\Faction;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\Route;
use EsterenMaps\Entity\RouteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ApiRouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.entities.common.name',
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('forcedDistance', NumberType::class, [
                'label' => 'admin.entities.routes.forced_distance',
                'help' => 'admin.entities.routes.distance_help',
                'constraints' => [
                    new Constraints\Range(['min' => 0]),
                ],
            ])
            ->add('guarded', CheckboxType::class, [
                'label' => 'admin.entities.routes.guarded',
                'constraints' => [
                    new Constraints\Type(['type' => 'bool']),
                ],
            ])
            ->add('routeType', EntityType::class, [
                'label' => 'admin.entities.common.type',
                'class' => RouteType::class,
            ])
            ->add('markerStart', EntityType::class, [
                'label' => 'admin.entities.routes.markerStart',
                'class' => Marker::class,
            ])
            ->add('markerEnd', EntityType::class, [
                'label' => 'admin.entities.routes.markerEnd',
                'class' => Marker::class,
            ])
            ->add('faction', EntityType::class, [
                'label' => 'admin.entities.faction',
                'class' => Faction::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', Route::class)
        ;
    }
}
