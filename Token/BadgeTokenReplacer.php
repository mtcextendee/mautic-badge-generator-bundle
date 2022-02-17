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
    private \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator $badgeUrlGenerator;

    /**
     * BadgeTokenReplacer constructor.
     */
    public function __construct(BadgeUrlGenerator $badgeUrlGenerator)
    {
        $this->badgeUrlGenerator = $badgeUrlGenerator;
    }

    /**
     * @param string $content
     */
    public function replaceTokens($content, Lead $contact = null): string
    {
        $tokens = $this->findTokens($content, $contact);

        return str_replace(array_keys($tokens), $tokens, $content);
    }

    /**
     * @param string    $content
     * @param Lead|null $contact
     */
    public function findTokens($content, $contact = null): array
    {
        $tokens    = [];
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
