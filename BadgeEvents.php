<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle;

/**
 * Class BadgeEvents
 * Events available for MauticBadgeGeneratorBundle.
 */
final class BadgeEvents
{
    /**
     * The mautic.badge_pre_save event is thrown right before a asset is persisted.
     *
     * The event listener receives a
     * MauticPlugin\MauticBadgeGeneratorBundle\Event\BadgeEvent instance.
     *
     * @var string
     */
    const PRE_SAVE = 'mautic.badge_pre_save';

    /**
     * The mautic.badge_post_save event is thrown right after a asset is persisted.
     *
     * The event listener receives a
     * MauticPlugin\MauticBadgeGeneratorBundle\Event\BadgeEvent instance.
     *
     * @var string
     */
    const POST_SAVE = 'mautic.badge_post_save';

    /**
     * The mautic.badge_pre_delete event is thrown prior to when a asset is deleted.
     *
     * The event listener receives a
     * MauticPlugin\MauticBadgeGeneratorBundle\Event\BadgeEvent instance.
     *
     * @var string
     */
    const PRE_DELETE = 'mautic.badge_pre_delete';

    /**
     * The mautic.badge_post_delete event is thrown after a asset is deleted.
     *
     * The event listener receives a
     * MauticPlugin\MauticBadgeGeneratorBundle\Event\BadgeEvent instance.
     *
     * @var string
     */
    const POST_DELETE = 'mautic.badge_post_delete';
}
