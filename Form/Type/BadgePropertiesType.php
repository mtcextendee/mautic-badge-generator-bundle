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

use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BadgePropertiesType extends AbstractType
{

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BadgePropertiesType constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper, TranslatorInterface $translator)
    {

        $this->integrationHelper = $integrationHelper;
        $this->translator        = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $integration = $this->integrationHelper->getIntegrationObject('BadgeGenerator');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished() === true) {
            $settings           = $integration->mergeConfigToFeatureSettings();
            $numberOfTextBlocks = ArrayHelper::getValue(
                'numberOfTextBlocks',
                $settings,
                BadgeGenerator::NUMBER_OF_DEFAULT_TEXT_BLOCKS
            );
            for ($i = 1; $i <= $numberOfTextBlocks; $i++) {
                $data          = ArrayHelper::getValue('text'.$i, $options['data'], []);
                $data['index'] = $i;
                $builder->add(
                    'text'.$i,
                    BadgeTextType::class,
                    [
                        'label' => false,
                        'data'  => $data,
                    ]
                );
            }

            $numberOfImagesBlocks = ArrayHelper::getValue(
                'numberOfImagesBlocks',
                $settings,
                BadgeGenerator::NUMBER_OF_DEFAULT_IMAGES_BLOCKS
            );
            for ($i = 1; $i <= $numberOfImagesBlocks; $i++) {
                $data          = ArrayHelper::getValue('image'.$i, $options['data'], []);
                $data['index'] = $i;
                $builder->add(
                    'image'.$i,
                    BadgeImageType::class,
                    [
                        'label' => false,
                        'data'  => $data,
                    ]
                );
            }

            $builder->add(
                'tags',
                'lead_tag',
                [
                    'add_transformer' => true,
                    'by_reference'    => false,
                    'label'           => 'mautic.plugin.badge.generator.form.tags',
                    'attr'            => [
                        'data-placeholder'     => $this->translator->trans('mautic.lead.tags.select_or_create'),
                        'data-no-results-text' => $this->translator->trans('mautic.lead.tags.enter_to_create'),
                        'data-allow-add'       => 'true',
                        'onchange'             => 'Mautic.createLeadTag(this)',
                    ],
                    'data'            => ArrayHelper::getValue('tags', $options['data']),
                ]
            );
        }

        $integration = $this->integrationHelper->getIntegrationObject('BarcodeGenerator');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished() === true) {
            $builder->add(
                'barcode',
                BadgeBarcodeType::class,
                [
                    'label'      => false,
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'data'       => ArrayHelper::getValue('barcode', $options['data']),
                ]
            );

            $builder->add(
                'qrcode',
                BadgeQrcodeType::class,
                [
                    'label'      => false,
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'data'       => ArrayHelper::getValue('qrcode', $options['data']),
                ]
            );
        }


        $builder->add(
            'mapping',
            BadgeMapping::class,
            [
                'label'      => false,
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'data'       => ArrayHelper::getValue('mapping', $options['data']),
            ]
        );


        $builder->add(
            'restriction',
            BadgeRestriction::class,
            [
                'label'      => false,
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'data'       => ArrayHelper::getValue('restriction', $options['data']),
            ]
        );
    }
}
