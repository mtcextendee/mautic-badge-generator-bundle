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

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Uploader\BadgeUploader;
use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BadgeGenerator
{
    CONST CUSTOM_FONT_CONFIG_PARAMETER = 'badge_custom_font_path_to_ttf';
    CONST NUMBER_OF_DEFAULT_TEXT_BLOCKS = 4;
    /**
     * @var BadgeModel
     */
    private $badgeModel;

    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var BadgeUploader
     */
    private $badgeUploader;

    /**
     * @var Lead|null
     */
    private $contact;

    /**
     * @var Badge
     */
    private $badge;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var RouterInterface
     */
    private $router;

    /** @var  string */
    private $fontName;

    /**
     * BadgeGenerator constructor.
     *
     * @param BadgeModel           $badgeModel
     * @param LeadModel            $leadModel
     * @param BadgeUploader        $badgeUploader
     * @param CoreParametersHelper $coreParametersHelper
     * @param IntegrationHelper    $integrationHelper
     * @param RouterInterface      $router
     */
    public function __construct(BadgeModel $badgeModel, LeadModel $leadModel, BadgeUploader $badgeUploader, CoreParametersHelper $coreParametersHelper, IntegrationHelper $integrationHelper, RouterInterface $router)
    {
        $this->badgeModel    = $badgeModel;
        $this->leadModel     = $leadModel;
        $this->badgeUploader = $badgeUploader;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper = $integrationHelper;
        $this->router = $router;
    }

    /**
     * @param      $badgeId
     * @param int  $leadId
     */
    public function generate($badgeId, $leadId)
    {
        if (!$badge = $this->badgeModel->getEntity($badgeId)) {
            throw new EntityNotFoundException(sprintf('Badge with ID "%s" not exist', $badgeId));
        }
        $this->badge   = $badge;
        $this->contact = !empty($leadId) ? $this->leadModel->getEntity($leadId) : null;

        $pdf = $this->loadFpdi();
        $pdf->setSourceFile($this->badgeUploader->getCompleteFilePath($badge, $badge->getSource()));
        // import page 1
        $tplIdx = $pdf->importPage(1);
        $width  = $badge->getWidth();
        $height = $badge->getHeight();
        $pdf->useTemplate($tplIdx, 0, 0, $width, $height, true);


        $integration = $this->integrationHelper->getIntegrationObject('BarcodeGenerator');

        $barcodeFields = ArrayHelper::getValue('fields', $badge->getProperties()['barcode'], false);

        if ($integration && $integration->getIntegrationSettings()->getIsPublished() === true && !empty($barcodeFields)) {
            $barcodeWidth = ArrayHelper::getValue('width', $badge->getProperties()['barcode'], 120);
            $barcodeHeight = ArrayHelper::getValue('height', $badge->getProperties()['barcode'], 50);
            $barcodePosition = ArrayHelper::getValue('position', $badge->getProperties()['barcode'], 50);

            $pdf->SetXY(0, $barcodePosition);

            $url = $this->router->generate(
                'mautic_barcode_generator',
                [
                    'value' => $this->getCustomTextFromFields('barcode'),
                    'token' => 'barcodePNG',
                    'type'=>'C128'
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );



            $pdf->Image($url, '', '', $barcodeWidth, $barcodeHeight, $link='', $align='', '', false, 300, 'C');
        }



        $integrationSettings = $this->integrationHelper->getIntegrationObject('BadgeGenerator')->mergeConfigToFeatureSettings();
        $numberOfTextBlocks = ArrayHelper::getValue('numberOfTextBlocks', $integrationSettings, self::NUMBER_OF_DEFAULT_TEXT_BLOCKS);

        for ($i = 1; $i <= $numberOfTextBlocks; $i++) {
            if (empty($badge->getProperties()['text'.$i])) {
                continue;
            }

            $fields = ArrayHelper::getValue('fields', $badge->getProperties()['text'.$i], false);
            if (empty($fields)) {
                continue;
            }

            $position = ArrayHelper::getValue('position', $badge->getProperties()['text'.$i], $i*20);
            $color = ArrayHelper::getValue('color', $badge->getProperties()['text'.$i], '000000');
            $fontSize = ArrayHelper::getValue('fontSize', $badge->getProperties()['text'.$i], 30);
            $pdf->SetFont($this->fontName, '', $fontSize);
            // reset position
            $pdf->SetXY(0, $position);
            // set color
            $hex = '#'.$color;
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            $pdf->SetTextColor($r, $g, $b);
            // create cell
            $pdf->Cell($width, 50,$this->getCustomText('text'.$i) , 0, 0, 'C');
        }



        $pdf->SetXY(0, $badge->getProperties()['text2']['position']);
        $hex = '#'.$badge->getProperties()['text2']['color'];
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Cell($width, 50, $this->getCustomText('text2'), 0, 0, 'C');



        // Stage auto mapping
        if ($this->contact && !empty($badge->getStage())) {
            $this->leadModel->addToStages($this->contact, $badge->getStage());
            $this->leadModel->saveEntity($this->contact);
        }

        echo $pdf->Output('custom_pdf_'.time().'.pdf', 'I');
        exit;
    }

    /**
     * @return Fpdi
     */
    private function loadFpdi()
    {
        $pdf = new Fpdi();

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

         if ($fontPath = $this->coreParametersHelper->getParameter(self::CUSTOM_FONT_CONFIG_PARAMETER)) {
            $this->fontName = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
            $pdf->SetFont($this->fontName, '', '30');
        }

        $pdf->AddPage();

        return $pdf;
    }

    private function getCustomText($block)
    {
        return $this->getCustomTextFromFields($block);
        //return utf8_encode('محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس');
        //return  iconv('UTF-8', 'windows-1252', 'محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس');
        return iconv('UTF-8', 'windows-1252', $this->getCustomTextFromFields($block));;
        return iconv("UTF-8", "Windows-1252//TRANSLIT", $this->getCustomTextFromFields($block));
    }

    /**
     * @param string $block
     *
     * @return string
     */
    private function getCustomTextFromFields($block)
    {
        $fields = $this->badge->getProperties()[$block]['fields'];
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $text   = [];
        foreach ($fields as $field) {
            $text[] = $this->contact ? $this->contact->getFieldValue($field) : $field;
        }

        return implode(' ', $text);
    }
}