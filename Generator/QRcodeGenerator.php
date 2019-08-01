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

class QRcodeGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * BarcodeGenerator constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Fpdi            $pdf
     * @param PropertiesCrate $propertiesCrate
     */
    public function writeToPdf(Fpdi $pdf, PropertiesCrate $propertiesCrate, ContactFieldCrate $contactFieldCrate)
    {
        $pdf->SetXY($propertiesCrate->getPositionX(), $propertiesCrate->getPositionY());
        $code = $contactFieldCrate->getCustomTextFromFields($propertiesCrate->getFields());

        if (empty($code)) {
            return;
        }

        $properties = $propertiesCrate->getProperties();
        if (!empty($properties['size'])) {
            $properties['size'] = $properties['size'] * 2;
        }
        $modifier = http_build_query($properties,null, ',');
        $url = $this->router->generate(
            'mautic_qrcode_generator',
            [
                'value' => $code,
                'options' => $modifier,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $pdf->Image(
            $url,
            $propertiesCrate->getPositionX(),
            $propertiesCrate->getPositionY(),
            $propertiesCrate->getWidth(),
            $propertiesCrate->getWidth(),
            $link = '',
            $align = '',
            '',
            false,
            300,
            $propertiesCrate->getAlign()
        );
    }

}