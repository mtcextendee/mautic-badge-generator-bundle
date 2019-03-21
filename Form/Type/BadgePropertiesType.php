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

class BadgePropertiesType extends AbstractType
{

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * BadgePropertiesType constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {

        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $integration = $this->integrationHelper->getIntegrationObject('BadgeGenerator');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished() === true) {
            $numberOfTextBlocks = ArrayHelper::getValue('numberOfTextBlocks', $integration->mergeConfigToFeatureSettings(), BadgeGenerator::NUMBER_OF_DEFAULT_TEXT_BLOCKS);
            for ($i = 1; $i <= $numberOfTextBlocks; $i++) {
                $builder->add(
                    'text'.$i,
                    BadgeTextType::class,
                    [
                        'label'       => false,
                    ]
                );
            }
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
                ]
            );
        }

    }
}
