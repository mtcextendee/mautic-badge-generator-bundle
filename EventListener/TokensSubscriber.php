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

use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TokensSubscriber.
 */
class TokensSubscriber implements EventSubscriberInterface
{
    /**
     * @var BadgeModel
     */
    private $badgeModel;

    /**
     * TokensSubscriber constructor.
     */
    public function __construct(BadgeModel $badgeModel)
    {
        $this->badgeModel = $badgeModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD   => ['onBuildBuilder', 0],
        ];
    }

    /**
     * Add field tokens to email.
     */
    public function onBuildBuilder(EmailBuilderEvent $event)
    {
        // register tokens
        $tokens = [];

        $badges = $this->badgeModel->getEntities();
        /** @var Badge $badge */
        foreach ($badges as $badge) {
            $tokens['{badge='.$badge->getId().'}'] = $badge->getName().' ('.$badge->getId().')';
        }
        if ($event->tokensRequested(array_keys($tokens))) {
            $event->addTokens(
                $event->filterTokens($tokens)
            );
        }
    }
}
