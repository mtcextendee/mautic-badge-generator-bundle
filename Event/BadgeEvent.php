<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;

class BadgeEvent extends CommonEvent
{
    /**
     * BadgeEvent constructor.
     *
     * @param bool $isNew
     */
    public function __construct(Badge $entity, $isNew = false)
    {
        $this->entity = $entity;
        $this->isNew  = $isNew;
    }

    /**
     * @return Badge
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity(Badge $entity)
    {
        $this->entity = $entity;
    }
}
