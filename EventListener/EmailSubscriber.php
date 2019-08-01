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
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeTokenReplacer;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonSubscriber
{

    /**
     * @var BadgeTokenReplacer
     */
    private $badgeTokenReplacer;

    public function __construct(BadgeTokenReplacer $badgeTokenReplacer)
    {
        $this->badgeTokenReplacer = $badgeTokenReplacer;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }


    /**
     * @param EmailSendEvent $event
     */
    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailGenerate(EmailSendEvent $event)
    {
        // Combine all possible content to find tokens across them
        $content = $event->getSubject();
        $content .= $event->getContent();
        $content .= $event->getPlainText();
        $content .= implode(' ', $event->getTextHeaders());

        $lead = $event->getLead();

        $tokenList = $this->badgeTokenReplacer->findTokens($content, $lead);

        if (count($tokenList)) {
            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }
}
