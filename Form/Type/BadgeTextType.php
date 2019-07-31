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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BadgeTextType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'fields',
            LeadFieldsType::class,
            [
                'label'      => 'mautic.plugin.badge.generator.form.fields',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required'   => false,
                'multiple'   => true,
            ]
        );

        $builder->add(
            'color',
            'text',
            [
                'label'      => 'mautic.focus.form.text_color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'align',
            'choice',
            [
                'choices'     => [
                    'C' => 'mautic.core.center',
                    'L' => 'mautic.core.left',
                ],
                'label'       => 'mautic.plugin.badge.generator.form.text.align',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class' => 'form-control',
                ],
                'required'    => false,
                'empty_value' => false,
            ]
        );

        $coreFonts = [
            "times",
            "symbol",
            "timesb",
            "timesi",
            "aefurat",
            "courier",
            "timesbi",
            "courierb",
            "courieri",
            "freemono",
            "freesans",
            "courierbi",
            "freemonob",
            "freemonoi",
            "freesansb",
            "freesansi",
            "freeserif",
            "helvetica",
            "pdfatimes",
            "dejavusans",
            "freemonobi",
            "freesansbi",
            "freeserifb",
            "freeserifi",
            "helveticab",
            "helveticai",
            "pdfasymbol",
            "pdfatimesb",
            "pdfatimesi",
            "freeserifbi",
            "aealarabiya",
            "dejavusansb",
            "dejavusansi",
            "dejavuserif",
            "helveticabi",
            "pdfacourier",
            "pdfatimesbi",
            "dejavusansbi",
            "dejavuserifb",
            "dejavuserifi",
            "pdfacourierb",
            "pdfacourieri",
        ];


        $builder->add(
            'font',
            'choice',
            [
                'choices'     => array_combine($coreFonts, $coreFonts),
                'empty_value' => '',
                'label'       => 'mautic.plugin.badge.generator.form.text.align',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class' => 'form-control',
                ],
                'required'    => false,
                'empty_value' => false,
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
            'fontSize',
            TextType::class,
            [
                'label'      => 'mautic.plugin.badge.generator.form.font.size',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required'   => false,
            ]
        );
    }
}
