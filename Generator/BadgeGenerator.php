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
use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Mautic\CoreBundle\Templating\Helper\AssetsHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate\ContactFieldCrate;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\Crate\PropertiesCrate;
use MauticPlugin\MauticBadgeGeneratorBundle\Model\BadgeModel;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeUrlGenerator;
use MauticPlugin\MauticBadgeGeneratorBundle\Uploader\BadgeUploader;
use setasign\Fpdi\Tcpdf\Fpdi;

class BadgeGenerator
{
    CONST CUSTOM_FONT_CONFIG_PARAMETER    = 'badge_custom_font_path_to_ttf';

    CONST NUMBER_OF_DEFAULT_TEXT_BLOCKS   = 4;

    CONST NUMBER_OF_DEFAULT_IMAGES_BLOCKS = 0;

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

    /** @var  string */
    private $fontName;

    /**
     * @var BarcodeGenerator
     */
    private $barcodeGenerator;

    /**
     * @var qrcodegenerator
     */
    private $QRcodeGenerator;

    /**
     * @var AssetsHelper
     */
    private $assetsHelper;

    /**
     * @var PathsHelper
     */
    private $pathsHelper;

    /**
     * @var BadgeUrlGenerator
     */
    private $badgeUrlGenerator;

    /**
     * BadgeGenerator constructor.
     *
     * @param BadgeModel           $badgeModel
     * @param LeadModel            $leadModel
     * @param BadgeUploader        $badgeUploader
     * @param CoreParametersHelper $coreParametersHelper
     * @param IntegrationHelper    $integrationHelper
     * @param BarcodeGenerator     $barcodeGenerator
     * @param QRcodeGenerator      $QRcodeGenerator
     * @param AssetsHelper         $assetsHelper
     * @param PathsHelper          $pathsHelper
     * @param BadgeUrlGenerator    $badgeUrlGenerator
     */
    public function __construct(
        BadgeModel $badgeModel,
        LeadModel $leadModel,
        BadgeUploader $badgeUploader,
        CoreParametersHelper $coreParametersHelper,
        IntegrationHelper $integrationHelper,
        BarcodeGenerator $barcodeGenerator,
        QRcodeGenerator $QRcodeGenerator,
        AssetsHelper $assetsHelper,
        PathsHelper $pathsHelper,
        BadgeUrlGenerator $badgeUrlGenerator
    ) {
        $this->badgeModel           = $badgeModel;
        $this->leadModel            = $leadModel;
        $this->badgeUploader        = $badgeUploader;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper    = $integrationHelper;
        $this->barcodeGenerator     = $barcodeGenerator;
        $this->QRcodeGenerator      = $QRcodeGenerator;
        $this->assetsHelper         = $assetsHelper;
        $this->pathsHelper          = $pathsHelper;
        $this->badgeUrlGenerator = $badgeUrlGenerator;
    }

    /**
     * @param      $badgeId
     * @param      $leadId
     * @param null $hash
     *
     * @param bool $isAdmin
     *
     * @throws EntityNotFoundException
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function generate($badgeId, $leadId, $hash = null, $isAdmin = false)
    {
        if (!$badge = $this->badgeModel->getEntity($badgeId)) {
            throw new EntityNotFoundException(sprintf('Badge with ID "%s" not exist', $badgeId));
        }
        $this->badge   = $badge;
        $this->contact = !empty($leadId) ? $this->leadModel->getEntity($leadId) : null;

        if ($this->contact && !$isAdmin) {
            $this->displayBadge($this->contact, $badge);
        }

        $pdf = $this->loadFpdi();
        $pdf->setSourceFile($this->badgeUploader->getCompleteFilePath($badge, $badge->getSource()));
        // import page 1
        $tplIdx = $pdf->importPage(1);
        $width  = $badge->getWidth();
        $height = $badge->getHeight();
        $pdf->useTemplate($tplIdx, 0, 0, $width, $height, true);


        $integration = $this->integrationHelper->getIntegrationObject('BarcodeGenerator');

        $barcodeProperties = ArrayHelper::getValue('barcode', $badge->getProperties(), []);

        $contactFieldCrate = new ContactFieldCrate($this->contact);

        if ($integration && $integration->getIntegrationSettings()->getIsPublished() === true) {

            // barcode
            $barcodePropertiesCrate = new PropertiesCrate(
                ArrayHelper::getValue('barcode', $badge->getProperties(), [])
            );
            if ($barcodePropertiesCrate->isEnabled()) {
                $this->barcodeGenerator->writeToPdf($pdf, $barcodePropertiesCrate, $contactFieldCrate);
            }

            // qrcode
            $qrcodePropertiesCrate = new PropertiesCrate(ArrayHelper::getValue('qrcode', $badge->getProperties(), []));
            if ($qrcodePropertiesCrate->isEnabled()) {
                $this->QRcodeGenerator->writeToPdf($pdf, $qrcodePropertiesCrate, $contactFieldCrate);
            }

        }


        $integrationSettings = $this->integrationHelper->getIntegrationObject(
            'BadgeGenerator'
        )->mergeConfigToFeatureSettings();
        $numberOfTextBlocks  = ArrayHelper::getValue(
            'numberOfTextBlocks',
            $integrationSettings,
            self::NUMBER_OF_DEFAULT_TEXT_BLOCKS
        );

        for ($i = 1; $i <= $numberOfTextBlocks; $i++) {
            if (empty($badge->getProperties()['text'.$i])) {
                continue;
            }

            $fields = ArrayHelper::getValue('fields', $badge->getProperties()['text'.$i], false);
            if (empty($fields)) {
                continue;
            }
            $positionY = ArrayHelper::getValue('position', $badge->getProperties()['text'.$i], $i * 20);
            $positionX = ArrayHelper::getValue('positionX', $badge->getProperties()['text'.$i], 0);
            $align     = ArrayHelper::getValue('align', $badge->getProperties()['text'.$i], 'C');
            $rtl     = ArrayHelper::getValue('rtl', $badge->getProperties()['text'.$i], false);

            $color     = ArrayHelper::getValue('color', $badge->getProperties()['text'.$i], '000000');
            $fontSize  = ArrayHelper::getValue('fontSize', $badge->getProperties()['text'.$i], 30);
            $lineHeight  = ArrayHelper::getValue('lineHeight', $badge->getProperties()['text'.$i], 1);
            $stretch   = ArrayHelper::getValue('stretch', $badge->getProperties()['text'.$i], 0);
            $style     = ArrayHelper::getValue('style', $badge->getProperties()['text'.$i], []);
            $font      = ArrayHelper::getValue('font', $badge->getProperties()['text'.$i], $this->fontName);
            if ($font == 'custom') {
                $ttf = ArrayHelper::getValue('ttf', $badge->getProperties()['text'.$i]);
                if ($ttf) {
                    $font = \TCPDF_FONTS::addTTFfont(
                        $this->badgeUploader->getCompleteFilePath($badge, $ttf),
                        'TrueTypeUnicode',
                        '',
                        96
                    );
                }
            }
            $pdf->SetFont($font, implode('', $style), $fontSize);

            // reset position
            $pdf->SetXY($positionX, $positionY);
            // set color
            $hex = '#'.$color;
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            $pdf->SetTextColor($r, $g, $b);
            // create cell
            $pdf->setCellHeightRatio($lineHeight);
            $pdf->setRTL((bool) $rtl);
            $pdf->MultiCell($width-$positionX, '', $this->getCustomText('text'.$i), 0, $align, false, 1, $positionX, $positionY, true, $stretch);
        }


        $numberOfImagesBlocks = ArrayHelper::getValue(
            'numberOfImagesBlocks',
            $integrationSettings,
            self::NUMBER_OF_DEFAULT_IMAGES_BLOCKS
        );

        for ($i = 1; $i <= $numberOfImagesBlocks; $i++) {
            if (empty($badge->getProperties()['image'.$i])) {
                continue;
            }

            $field = ArrayHelper::getValue('fields', $badge->getProperties()['image'.$i], false);
            if (empty($field)) {
                continue;
            }

            $positionY = ArrayHelper::getValue('position', $badge->getProperties()['image'.$i], 0);
            $positionX = ArrayHelper::getValue('positionX', $badge->getProperties()['image'.$i], 0);
            $width     = ArrayHelper::getValue('width', $badge->getProperties()['image'.$i], 100);
            $height    = ArrayHelper::getValue('height', $badge->getProperties()['image'.$i], 100);
            $align     = ArrayHelper::getValue('align', $badge->getProperties()['image'.$i], 'C');
            $rounded     = ArrayHelper::getValue('rounded', $badge->getProperties()['image'.$i], false);
            if ($align !== 'C') {
                $align = '';
            }

            // reset position
            //  $pdf->SetXY($positionX, $positionY);
            $image = '';
            if ($hash) {
                if (!empty($this->badge->getProperties()['image'.$i]['avatar'])) {
                    $image = $this->getAvatar();
                } else {
                    $image = $this->getCustomImage('image'.$i);
                }
            } else {
                $image = $this->getCustomImage('image'.$i, 'https://placehold.co/300x300.png');
            }

            if ($rounded) {
               // $image = $this->badgeUrlGenerator->getLinkToRoundedImage($image);
            }
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                switch (exif_imagetype($image)) {
                    case IMG_GIF:
                        $type = 'GIF';
                        break;
                    case IMG_JPG:
                        $type = 'JPG';
                        break;
                        break;
                    case IMG_PNG:
                    case 3:
                        $type = 'PNG';
                        break;
                    default:
                        $type = 'JPG';
                }
//Start Graphic Transformation
                // $pdf->StartTransform();

// set clipping mask
                //  $pdf->Circle($positionX+($width/2), ($height/2)+$positionY, ($width/2)-20, 0,360,  'CNZ', [], [255,255,2]);
                $pdf->Image(
                    $image,
                    $positionX,
                    $positionY,
                    $width,
                    $height,
                    $type,
                    '',
                    'C',
                    false,
                    150,
                    $align,
                    false,
                    false,
                    0,
                    true,
                    false,
                    true
                );
            }

            //$pdf->StopTransform();

        }


        /*$pdf->SetXY(0, $badge->getProperties()['text2']['position']);
        $hex = '#'.$badge->getProperties()['text2']['color'];
        list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
        $pdf->SetTextColor($r, $g, $b);
        $pdf->Cell($width, 50, $this->getCustomText('text2'), 0, 0, 'C');*/


        // Stage auto mapping
        if ($this->contact) {
            if (!empty($badge->getStage())) {
                $this->leadModel->addToStages($this->contact, $badge->getStage());
            }
            if (!empty($badge->getProperties()['mapping']['segment'])) {
                $this->leadModel->addToLists($this->contact, [$badge->getProperties()['mapping']['segment']]);
            }
            if (!empty($this->contact->getChanges())) {
                $this->leadModel->saveEntity($this->contact);
            }
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

    /**
     * @param string $block
     * @param string $default
     *
     * @return string
     */
    private function getCustomImage($block, $default = null)
    {
        return $this->getCustomTextFromFields($block, $default);
    }

    /**
     * @param $block
     *
     * @return string
     */
    private function getCustomText($block)
    {
        return $this->getCustomTextFromFields($block);
        //return utf8_encode('محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس');
        //return  iconv('UTF-8', 'windows-1252', 'محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس محمد فهد الحواس');
        //return iconv('UTF-8', 'windows-1252', $this->getCustomTextFromFields($block));;
        //return iconv("UTF-8", "Windows-1252//TRANSLIT", $this->getCustomTextFromFields($block));
    }

    /**
     * @param $alias
     *
     * @return string
     */
    private function getContactFieldValue($alias, $default = null)
    {
        return $this->contact ? $this->contact->getFieldValue($alias) : ($default ? $default : $alias);
    }

    /**
     * @param string $block
     *
     * @return string
     */
    private function getCustomTextFromFields($block, $default = null)
    {
        $fields = $this->badge->getProperties()[$block]['fields'];
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $text = [];
        foreach ($fields as $field) {
            $text[] = $this->getContactFieldValue($field, $default);
        }

        return implode(' ', $text);
    }


    /**
     * @return string
     */
    private function getAvatar()
    {
        $preferred = $this->contact->getPreferredProfileImage();

        if ($preferred == 'custom') {
            $avatarPath = $this->getAvatarPath(true).'/avatar'.$this->contact->getId();
            if (file_exists($avatarPath) && $fmtime = filemtime($avatarPath)) {
                // Append file modified time to ensure the latest is used by browser
                return $this->assetsHelper->getUrl(
                    $this->getAvatarPath().'/avatar'.$this->contact->getId().'?'.$fmtime,
                    null,
                    null,
                    true,
                    true
                );
            }
        }
    }

    /**
     * Get avatar path.
     *
     * @param $absolute
     *
     * @return string
     */
    private function getAvatarPath($absolute = false)
    {
        $imageDir = $this->pathsHelper->getSystemPath('images', $absolute);

        return $imageDir.'/lead_avatars';
    }

    /**
     * @param Lead  $contact
     * @param Badge $badge
     *
     * @return void
     * @throws \Exception
     */
    public function displayBadge(Lead $contact, Badge $badge)
    {
        $this->displaBasedOnTags($contact, $badge);
        $this->displayBasedOnSegment($contact, $badge);
    }

    /**
     * @param Lead  $contact
     * @param Badge $badge
     *
     * @return bool
     * @throws \Exception
     */
    private function displayBasedOnSegment(Lead $contact, Badge $badge)
    {
        $restriction = ArrayHelper::getValue('restriction', $badge->getProperties(), []);
        $segments = ArrayHelper::getValue('segment', $restriction, []);
        if (empty($segments)) {
            return true;
        }
        $contactSegments = $this->leadModel->getLists($contact);
        foreach ($contactSegments as $contactSegment) {
            if (in_array($contactSegment->getId(), $segments)) {
                return true;
            }
        }

        throw new \Exception('Access denied');
    }

    /**
     * @param Lead  $contact
     * @param Badge $badge
     *
     * @return bool
     * @throws \Exception
     */
    private function displaBasedOnTags(Lead $contact, Badge $badge)
    {
        $tags = ArrayHelper::getValue('tags', $badge->getProperties());
        if (empty($tags)) {
            return true;
        }

        $contactTags = $contact->getTags()->getKeys();
        foreach ($contactTags as $contactTag) {
            if (in_array($contactTag, $badge->getProperties()['tags'])) {
                return true;
            }
        }

        throw new \Exception('Access denied');
    }

}