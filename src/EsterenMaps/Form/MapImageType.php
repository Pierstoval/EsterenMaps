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

use EsterenMaps\Model\MapImageQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This form type is used to validate query parameters,
 *   when using the ApiTilesController with the API to generate an image.
 */
class MapImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ratio', IntegerType::class)
            ->add('width', IntegerType::class)
            ->add('height', IntegerType::class)
            ->add('x', IntegerType::class)
            ->add('y', IntegerType::class)
            ->add('withImages', IntegerType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MapImageQuery::class,
            'csrf_protection' => false,
        ]);
    }
}
