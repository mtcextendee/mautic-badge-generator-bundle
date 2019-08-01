<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate;

use Mautic\LeadBundle\Entity\Lead;

class ContactFieldCrate
{
    /**
     * @var Lead
     */
    private $contact;

    /**
     * ContactFieldCrate constructor.
     *
     * @param Lead|null $contact
     */
    public function __construct($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    private function getContactFieldValue($alias)
    {
        return $this->contact ? $this->contact->getFieldValue($alias) : $alias;
    }

    /**
     * @param array|string $fields
     *
     * @return string
     */
    public function getCustomTextFromFields($fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $text   = [];
        foreach ($fields as $field) {
            $text[] = $this->getContactFieldValue($field);
        }
        return implode(' ', $text);
    }
}