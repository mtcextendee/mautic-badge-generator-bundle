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

use Endroid\QrCode\ErrorCorrectionLevel;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\LeadBundle\Form\Type\LeadFieldsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BadgeQrcodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'contactId',
            YesNoButtonGroupType::class,
            [
                'label' => 'mautic.plugin.badge.generator.form.barcode.field.id',
                'attr'  => [
                ],
                'data'  => isset($options['data']['contactId']) ? $options['data']['contactId'] : false,
            ]
        );

        //badge_properties_barcode_contactId_1
        $builder->add(
            'fields',
            LeadFieldsType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.qrcode.field',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'        => 'form-control',
                    'data-show-on' => '{"badge_properties_qrcode_contactId_0":"checked"}',
                ],
                'required'    => false,
                'placeholder' => '',
                'multiple'    => false,
            ]
        );

        $builder->add(
            'align',
            ChoiceType::class,
            [
                'choices' => array_flip([
                    'C'=> 'mautic.core.center',
                    '' => 'mautic.core.left',
                ]),
                'label'      => 'mautic.plugin.badge.generator.form.text.align',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'position',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.text.position.y',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'positionX',
            TextType::class,
            [
                'label'       => 'mautic.plugin.badge.generator.form.text.position.x',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'size',
            NumberType::class,
            [
                'label'      => 'mautic.plugin.barcode.form.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'margin',
            NumberType::class,
            [
                'label'      => 'mautic.plugin.barcode.form.margin',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'fgcolor',
            TextType::class,
            [
                'label'      => 'mautic.plugin.barcode.form.fgcolor',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-toggle'  => 'color',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'bgcolor',
            TextType::class,
            [
                'label'      => 'mautic.plugin.barcode.form.bgcolor',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-toggle'  => 'color',
                ],
                'required' => false,
            ]
        );

        $builder->add('error_correction_level', ChoiceType::class, [
            'choices'  => array_flip([
                ErrorCorrectionLevel::LOW     => ErrorCorrectionLevel::LOW,
                ErrorCorrectionLevel::QUARTILE=> ErrorCorrectionLevel::QUARTILE,
                ErrorCorrectionLevel::MEDIUM  => ErrorCorrectionLevel::MEDIUM,
                ErrorCorrectionLevel::HIGH    => ErrorCorrectionLevel::HIGH,
            ]),
            'label'      => 'mautic.plugin.barcode.form.error_correction_level',
            'required'   => true,
            'placeholder'=> false,
            'attr'       => [
            ],
        ]);
    }
}
