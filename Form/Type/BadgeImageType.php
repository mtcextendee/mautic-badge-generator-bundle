<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Form\Type;

use Mautic\LeadBundle\Form\Type\LeadFieldsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BadgeImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'avatar',
            'yesno_button_group',
            [
                'label' => 'mautic.plugin.badge.generator.form.avatar',
                'attr'  => [
                ],
                'data'  => isset($options['data']['avatar']) ? $options['data']['avatar'] : false,
            ]
        );

        $builder->add(
            'fields',
            LeadFieldsType::class,
            [
                'label'      => 'mautic.plugin.badge.generator.form.fields',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'data-show-on' => '{"badge_properties_image'.$options['data']['index'].'_avatar_0":"checked"}',
                ],
                'required'   => false,
                'multiple'   => false,
            ]
        );

        $builder->add(
            'align',
            'choice',
            [
                'choices' => [
                    'C'=>'mautic.core.center',
                    '' => 'mautic.core.left',
                ],
                'label'      => 'mautic.plugin.badge.generator.form.text.align',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required'    => false,
                'empty_value' => false,
            ]
        );

        $builder->add(
            'width',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.barcode.width',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'height',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.barcode.height',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'position',
            TextType::class,
            [
                'label'      => 'mautic.plugin.badge.generator.form.text.position.y',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'positionX',
            TextType::class,
            [
                'label'      => 'mautic.plugin.badge.generator.form.text.position.x',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required'   => false,
            ]
        );

    }
}
