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

use Mautic\LeadBundle\Entity\Lead;

class BadgeTokenReplacer
{

    /**
     * @var BadgeUrlGenerator
     */
    private $badgeUrlGenerator;

    /**
     * BadgeTokenReplacer constructor.
     *
     * @param BadgeUrlGenerator $badgeUrlGenerator
     */
    public function __construct(BadgeUrlGenerator $badgeUrlGenerator)
    {
        $this->badgeUrlGenerator = $badgeUrlGenerator;
    }

    /**
     * @param string $content
     * @param Lead|null $contact
     *
     * @return array
     */
    public function replaceTokens($content, Lead $contact = null)
    {
        $tokens = $this->findTokens($content, $contact);

        return str_replace(array_keys($tokens), $tokens, $content);

    }

    /**
     * @param string $content
     * @param Lead|null $contact
     *
     * @return array
     */
    public function findTokens($content, $contact = null)
    {
        $tokens = [];
        $contactId = 0;
        if ($contact instanceof Lead && $contact->getId()) {
            $contactId = $contact->getId();
        } elseif (is_array($contact) && !empty($contact['id'])) {
            $contactId = $contact['id'];
        }

        preg_match_all('/{badge=(.*?)}/', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key => $badgeId) {
                $token = $matches[0][$key];

                if (isset($tokens[$token])) {
                    continue;
                }
                $tokens[$token] = $this->badgeUrlGenerator->getLink($badgeId, $contactId);
            }
        }

        return $tokens;
    }
}
