<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Token;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\LeadBundle\Entity\Lead;

class BadgeHashGenerator
{

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var CorePermissions
     */
    private $corePermissions;

    /**
     * BadgeHashGenerator constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, CorePermissions $corePermissions)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->corePermissions = $corePermissions;
    }

    /**
     * @param string $contactId
     * @param string $hash
     *
     * @return bool
     */
    public function isValidHash($contactId, $hash)
    {
        if ($this->corePermissions->isAdmin()) {
            return true;
        }

        if ($this->getHashId($contactId) == $hash) {
            return true;
        }

        return false;

    }

    /**
     * @param Lead $contact
     *
     * @return string
     */
    public function getHashId($contactId)
    {
       $key =  $contactId.'-'.$this->coreParametersHelper->getParameter('secret_key');
       return hash('sha1', $key);
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->corePermissions->isAdmin();
    }
}
