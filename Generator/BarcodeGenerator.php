<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Generator;

use MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate\ContactFieldCrate;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate\PropertiesCrate;
use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BarcodeGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * BarcodeGenerator constructor.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function writeToPdf(Fpdi $pdf, PropertiesCrate $propertiesCrate, ContactFieldCrate $contactFieldCrate)
    {
        $pdf->SetXY($propertiesCrate->getPositionX(), $propertiesCrate->getPositionY());
        $code = $contactFieldCrate->getCustomTextFromFields($propertiesCrate->getFields());

        if (empty($code)) {
            return;
        }

        $url = $this->router->generate(
            'mautic_barcode_generator',
            [
                'value' => $code,
                'token' => 'barcodePNG',
                'type'  => 'C128',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $pdf->Image(
            $url,
            $propertiesCrate->getPositionX(),
            $propertiesCrate->getPositionY(),
            $propertiesCrate->getWidth(),
            $propertiesCrate->getHeight(),
            $link = '',
            $align = '',
            '',
            false,
            300,
            $propertiesCrate->getAlign()
        );
    }
}
