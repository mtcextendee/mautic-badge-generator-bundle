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

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\LeadBundle\Form\Type\LeadFieldsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BadgeImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'avatar',
            YesNoButtonGroupType::class,
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
                    'class'        => 'form-control',
                    'data-show-on' => '{"badge_properties_image'.$options['data']['index'].'_avatar_0":"checked"}',
                ],
                'required'   => false,
                'multiple'   => false,
            ]
        );

        /* $builder->add(
             'flag',
             'yesno_button_group',
             [
                 'label' => 'mautic.plugin.badge.generator.form.flag',
                 'attr'  => [
                     'data-show-on' => '{"badge_properties_image'.$options['data']['index'].'_fields":"country"}',
                 ],
                 'data'  => isset($options['data']['flag']) ? $options['data']['flag'] : false,
             ]
         );*/

        $builder->add(
            'rounded',
            YesNoButtonGroupType::class,
            [
                'label' => 'mautic.plugin.badge.generator.form.rounded',
                'attr'  => [
                ],
                'data'  => isset($options['data']['rounded']) ? $options['data']['rounded'] : false,
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
                    'class'        => 'form-control',
                    'data-show-on' => '{"badge_properties_image'.$options['data']['index'].'_rounded_0":"checked"}',
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

        $builder->add(
            'align',
            ChoiceType::class,
            [
                'choices' => [
                    'C'=> 'mautic.core.center',
                    '' => 'mautic.core.left',
                ],
                'label'      => 'mautic.plugin.badge.generator.form.text.align',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required'    => false,
                'placeholder' => false,
            ]
        );
    }
}
