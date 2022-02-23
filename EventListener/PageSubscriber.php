<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\EventListener;

use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PageBundle\PageEvents;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    private \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer $badgeTokenReplacer;

    private ContactTracker $contactTracker;

    private CorePermissions $security;

    /**
     * PageSubscriber constructor.
     */
    public function __construct(BadgeTokenReplacer $badgeTokenReplacer, ContactTracker $contactTracker, CorePermissions $security)
    {
        $this->badgeTokenReplacer = $badgeTokenReplacer;
        $this->security = $security;
        $this->contactTracker = $contactTracker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
        ];
    }

    public function onPageDisplay(PageDisplayEvent $event): void
    {
        $content = $event->getContent();
        $lead    = ($this->security->isAnonymous()) ? $this->contactTracker->getContact() : null;
        $content = $this->badgeTokenReplacer->replaceTokens($content, $lead);
        $event->setContent($content);
    }
}
