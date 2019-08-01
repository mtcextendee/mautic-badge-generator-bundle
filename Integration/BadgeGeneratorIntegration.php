<?php


/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Integration;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilder;

class BadgeGeneratorIntegration extends AbstractIntegration
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'BadgeGenerator';
    }

    public function getIcon()
    {
        return 'plugins/MauticBadgeGeneratorBundle/Assets/img/logo.png';
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features') {
            $builder->add(
                'disable_in_contact_list',
                'yesno_button_group',
                [
                    'label' => 'mautic.plugin.badge.generator.form.disable_in_contact_list',
                    'attr'  => [
                    ],
                    'data'  => isset($data['disable_in_contact_list']) ? $data['disable_in_contact_list'] : false,
                ]
            );

            $builder->add(
                'numberOfTextBlocks',
                NumberType::class,
                [
                    'label'      => 'mautic.plugin.badge.generator.form.number.of.text.blocks',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'required'   => false,
                    'data'       => isset($data['numberOfTextBlocks']) ? $data['numberOfTextBlocks'] : 2,
                ]
            );
        }
    }
}
