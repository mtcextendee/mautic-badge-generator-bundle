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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BadgeUrlGenerator
{

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var BadgeHashGenerator
     */
    private $badgeHashGenerator;

    /**
     * BarcodeTokenReplacer constructor.
     *
     * @param RouterInterface    $router
     * @param BadgeHashGenerator $badgeHashGenerator
     */
    public function __construct(RouterInterface $router, BadgeHashGenerator $badgeHashGenerator)
    {
        $this->router = $router;
        $this->badgeHashGenerator = $badgeHashGenerator;
    }

    /**
     * @param     $badgeId
     * @param int $contactId
     *
     * @return string
     */
    public function getLink($badgeId, $contactId = 0)
    {
        return $this->router->generate(
            'mautic_badge_generator_generate',
            [
                'objectId' => $badgeId,
                'contactId' => $contactId,
                'hash' => $this->badgeHashGenerator->getHashId($contactId),

            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
