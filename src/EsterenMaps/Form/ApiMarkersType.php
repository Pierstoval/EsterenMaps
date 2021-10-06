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

namespace EsterenMaps\Form;

use EsterenMaps\Entity\Faction;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\MarkerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ApiMarkersType extends AbstractType
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
            ->add('markerType', EntityType::class, [
                'class' => MarkerType::class,
                'label' => 'admin.entities.common.type',
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
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
            ->setDefault('data_class', Marker::class)
        ;
    }
}
