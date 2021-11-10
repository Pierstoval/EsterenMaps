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
use EsterenMaps\Entity\Zone;
use EsterenMaps\Entity\ZoneType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ApiZoneType extends AbstractType
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
            ->add('zoneType', EntityType::class, [
                'label' => 'admin.entities.common.type',
                'class' => ZoneType::class,
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
            ->setDefault('data_class', Zone::class)
        ;
    }
}
