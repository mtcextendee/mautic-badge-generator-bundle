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

use Mautic\CoreBundle\Helper\EncryptionHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BadgeUrlGenerator
{
    private \Symfony\Component\Routing\RouterInterface $router;

    private \MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeHashGenerator $badgeHashGenerator;

    private \Mautic\CoreBundle\Helper\EncryptionHelper $encryptionHelper;

    /**
     * BarcodeTokenReplacer constructor.
     */
    public function __construct(RouterInterface $router, BadgeHashGenerator $badgeHashGenerator, EncryptionHelper $encryptionHelper)
    {
        $this->router             = $router;
        $this->badgeHashGenerator = $badgeHashGenerator;
        $this->encryptionHelper   = $encryptionHelper;
    }

    /**
     * @param     $badgeId
     * @param int $contactId
     */
    public function getLink($badgeId, $contactId = 0): string
    {
        return $this->router->generate(
            'mautic_badge_generator_generate',
            [
                'objectId'  => $badgeId,
                'contactId' => $contactId,
                'hash'      => $this->badgeHashGenerator->getHashId($contactId),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param string $imageBlock
     */
    public function getLinkToRoundedImage($imageUrl, $width): string
    {
        return $this->router->generate(
            'mautic_badge_generator_image_rounded',
            [
                'encryptImageUrl' => serialize($this->encryptionHelper->encrypt($imageUrl)),
                'width'           => $width,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
