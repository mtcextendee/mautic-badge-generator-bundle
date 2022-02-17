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
use Mautic\CoreBundle\Event\CustomButtonEvent;
use Mautic\CoreBundle\Templating\Helper\ButtonHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ButtonSubscriber implements EventSubscriberInterface
{
    private $event;

    private $objectId;

    private \MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel $badgeModel;

    private \Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper;

    private \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator $badgeUrlGenerator;

    private \MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator $badgeGenerator;

    private TranslatorInterface $translator;

    /**
     * ButtonSubscriber constructor.
     */
    public function __construct(BadgeModel $badgeModel, IntegrationHelper $integrationHelper, BadgeUrlGenerator $badgeUrlGenerator, BadgeGenerator $badgeGenerator, TranslatorInterface $translator)
    {
        $this->badgeModel        = $badgeModel;
        $this->integrationHelper = $integrationHelper;
        $this->badgeUrlGenerator = $badgeUrlGenerator;
        $this->badgeGenerator    = $badgeGenerator;
        $this->translator        = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectViewButtons', 0],
        ];
    }

    public function injectViewButtons(CustomButtonEvent $event): void
    {
        $integration = $this->integrationHelper->getIntegrationObject('BadgeGenerator');
        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }
        // disabled in plugin settings
        $settings = $integration->mergeConfigToFeatureSettings();
        if (!empty($settings['disable_in_contact_list'])) {
            return;
        }

        if (false === strpos($event->getRoute(), 'mautic_contact_')) {
            return;
        }
        if (null === $event->getItem()) {
            return;
        }

        $this->setEvent($event);

        /** @var Lead $object */
        $object = $event->getItem();
        if (method_exists($object, 'getId')) {
            $this->setObjectId($event->getItem()->getId());
        }

        $badges = $this->badgeModel->getEntities();
        /** @var Badge $badge */
        foreach ($badges as $badge) {
            try {
                $this->badgeGenerator->displayBadge($object, $badge);
            } catch (\Exception $exception) {
                continue;
            }
            $this->addButtonGenerator(
                $badge->getId(),
                $badge->getName(),
                'fa fa-external-link',
                'contact',
                -5,
                '_blank'
            );
        }
    }

    /**
     * @param        $objectId
     * @param        $btnText
     * @param        $icon
     * @param        $context
     * @param int    $priority
     * @param null   $target
     * @param string $header
     */
    private function addButtonGenerator($objectId, $btnText, $icon, $context, $priority = 1, $target = null, $header = ''): void
    {
        $event    = $this->getEvent();

        $route = $this->badgeUrlGenerator->getLink($objectId, $this->getObjectId());

        $attr     = [
            'href'        => $route,
            'data-toggle' => 'ajax',
            'data-method' => 'POST',
        ];

        switch ($target) {
            case '_blank':
                $attr['data-toggle'] = '';
                $attr['data-method'] = '';
                $attr['target']      = $target;
                break;
            case '#MauticSharedModal':
                $attr['data-toggle'] = 'ajaxmodal';
                $attr['data-method'] = '';
                $attr['data-target'] = $target;
                $attr['data-header'] = $header;
                break;
        }

        $button =
            [
                'attr'      => $attr,
                'btnText'   => $this->translator->trans($btnText),
                'iconClass' => $icon,
                'priority'  => $priority,
            ];
        // list view
        $event
            ->addButton(
                $button,
                ButtonHelper::LOCATION_LIST_ACTIONS,
                'mautic_'.$context.'_index'
            );

        // detail button
        $event
            ->addButton(
                $button,
                ButtonHelper::LOCATION_PAGE_ACTIONS,
                ['mautic_'.$context.'_action', ['objectAction' => 'view']]
            );
    }

    /**
     * @return CustomButtonEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed CustomButtonEvent
     */
    public function setEvent($event): void
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param mixed $objectId
     */
    public function setObjectId($objectId): void
    {
        $this->objectId = $objectId;
    }
}
