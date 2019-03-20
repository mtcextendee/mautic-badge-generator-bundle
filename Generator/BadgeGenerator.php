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
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Uploader\BadgeUploader;
use setasign\Fpdi\Tcpdf\Fpdi;

class BadgeGenerator
{
    CONST CUSTOM_FONT_CONFIG_PARAMETER = 'badge_custom_font_path_to_ttf';
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
     * BadgeGenerator constructor.
     *
     * @param BadgeModel           $badgeModel
     * @param LeadModel            $leadModel
     * @param BadgeUploader        $badgeUploader
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(BadgeModel $badgeModel, LeadModel $leadModel, BadgeUploader $badgeUploader, CoreParametersHelper $coreParametersHelper)
    {

        $this->badgeModel    = $badgeModel;
        $this->leadModel     = $leadModel;
        $this->badgeUploader = $badgeUploader;
        $this->coreParametersHelper = $coreParametersHelper;
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


        // reset position
        $pdf->SetXY(0, $badge->getProperties()['text1']['position']);
        // set color
        $hex = '#'.$badge->getProperties()['text1']['color'];
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        $pdf->SetTextColor($r, $g, $b);
        // create cell
        $pdf->Cell($width, 50, $this->getCustomText('text1'), 0, 0, 'C');

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

         if ($fontPath = $this->coreParametersHelper->getParameter(self::CUSTOM_FONT_CONFIG_PARAMETER)) {
            $fontName = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
            $pdf->SetFont($fontName, '', '30');
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
        $text   = [];

        foreach ($fields as $field) {
            $text[] = $this->contact ? $this->contact->getFieldValue($field) : $field;
        }

        return implode(' ', $text);
    }
}