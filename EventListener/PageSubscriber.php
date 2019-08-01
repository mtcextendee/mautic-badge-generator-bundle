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

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PageBundle\PageEvents;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer;

class PageSubscriber extends CommonSubscriber
{


    /**
     * @var BadgeTokenReplacer
     */
    private $badgeTokenReplacer;

    /**
     * PageSubscriber constructor.
     *
     * @param BadgeTokenReplacer $badgeTokenReplacer
     */
    public function __construct(BadgeTokenReplacer $badgeTokenReplacer)
    {
        $this->badgeTokenReplacer = $badgeTokenReplacer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
        ];
    }

    /**
     * @param PageDisplayEvent $event
     */
    public function onPageDisplay(PageDisplayEvent $event)
    {
        $content = $event->getContent();
        $lead    = ($this->security->isAnonymous()) ? $this->leadModel->getCurrentLead() : null;
        $content = $this->badgeTokenReplacer->replaceTokens($content, $lead);
        $event->setContent($content);
    }
}
