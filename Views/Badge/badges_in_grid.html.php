<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
/** @var \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator $badgeUrlGenerator */
?>
<div  style="position: absolute; left: 30px; bottom: 25px">
<?php
/** @var \MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge $badge */
foreach ($badges as $badge) {
    echo '<a  target="_blank" title="'.$badge->getName().'" href="'.$badgeUrlGenerator->getLink($badge->getId(), $contact->getId()).'" class="label label-primary">PDF</a> ';
}
?>
</div>