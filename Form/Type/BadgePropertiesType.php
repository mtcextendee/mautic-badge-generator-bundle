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

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

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

        $builder->add(
            'text1',
            BadgeTextType::class,
            [
                'label'       => false,
            ]
        );


        $builder->add(
            'text2',
            BadgeTextType::class,
            [
                'label'       => false,
            ]
        );

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
