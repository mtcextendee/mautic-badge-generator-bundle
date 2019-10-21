<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\TemplatingHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator;

class InjectCustomContentSubscriber extends CommonSubscriber
{

    /**
     * @var TemplatingHelper
     */
    private $templatingHelper;

    /**
     * @var BadgeModel
     */
    private $badgeModel;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $badges;

    /**
     * @var BadgeUrlGenerator
     */
    private $badgeUrlGenerator;

    /**
     * @var BadgeGenerator
     */
    private $badgeGenerator;


    /**
     * InjectCustomContentSubscriber constructor.
     *
     * @param TemplatingHelper  $templatingHelper
     * @param BadgeModel        $badgeModel
     * @param IntegrationHelper $integrationHelper
     * @param BadgeUrlGenerator $badgeUrlGenerator
     * @param BadgeGenerator    $badgeGenerator
     */
    public function __construct(TemplatingHelper $templatingHelper, BadgeModel $badgeModel, IntegrationHelper $integrationHelper, BadgeUrlGenerator $badgeUrlGenerator, BadgeGenerator $badgeGenerator)
    {

        $this->templatingHelper = $templatingHelper;
        $this->badgeModel = $badgeModel;
        $this->integrationHelper = $integrationHelper;
        $this->badges = $this->badgeModel->getEntities();
        $this->badgeUrlGenerator = $badgeUrlGenerator;
        $this->badgeGenerator = $badgeGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['injectViewCustomContent', 0],
        ];
    }

    /**
     * @param CustomContentEvent $customContentEvent
     */
    public function injectViewCustomContent(CustomContentEvent $customContentEvent)
    {
        $integration = $this->integrationHelper->getIntegrationObject('BadgeGenerator');
        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }

        $parameters = $customContentEvent->getVars();
        if ($customContentEvent->getContext() != 'lead.grid') {
            return;
        }else if (!isset($parameters['contact']) || !$parameters['contact'] instanceof Lead) {
            return;
        }
        $contact = $parameters['contact'];
        $badges = [];
        /** @var Badge $badge */
        foreach ($this->badges as $key=>$badge) {
            try {
                $this->badgeGenerator->displayBadge($contact, $badge);
                $badges[] = $badge;
            } catch (\Exception $exception) {
                continue;
            }
        }
        $content = $this->templatingHelper->getTemplating()->render(
            'MauticBadgeGeneratorBundle:Badge:badges_in_grid.html.php',
            [
                'badges'=>$badges,
                'contact'=>$contact,
                'badgeUrlGenerator'=>$this->badgeUrlGenerator
            ]
        );
        $customContentEvent->addContent($content);

    }


}
