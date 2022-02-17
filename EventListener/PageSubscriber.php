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

use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PageBundle\PageEvents;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    private \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer $badgeTokenReplacer;

    private \Mautic\LeadBundle\Model\LeadModel $leadModel;

    /**
     * PageSubscriber constructor.
     */
    public function __construct(BadgeTokenReplacer $badgeTokenReplacer, LeadModel $leadModel)
    {
        $this->badgeTokenReplacer = $badgeTokenReplacer;
        $this->leadModel          = $leadModel;
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
        $lead    = ($this->security->isAnonymous()) ? $this->leadModel->getCurrentLead() : null;
        $content = $this->badgeTokenReplacer->replaceTokens($content, $lead);
        $event->setContent($content);
    }
}
