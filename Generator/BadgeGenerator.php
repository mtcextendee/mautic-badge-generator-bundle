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
        $this->badgeUrlGenerator    = $badgeUrlGenerator;
    }

    /**
     * @param Lead $contact
     *
     * @return array
     */
    private function getContactBadges(Lead $contact)
    {
        $badges        = $this->badgeModel->getEntities();
        $contactBadges = [];
        /** @var Badge $badge */
        foreach ($badges as $badge) {
            try {
                $this->displayBadge($contact, $badge);
                $contactBadges[] = $badge;
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $contactBadges;
    }


    public function generateBatch(array $contactIds)
    {
        $contacts = $this->leadModel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'l.id',
                            'expr'   => 'in',
                            'value'  => $contactIds,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );

        $pdf = $this->loadFpdi();
        $i   = 0;
        foreach ($contacts as $contact) {
            $contactBadges = $this->getContactBadges($contact);
            if (empty($contactBadges)) {
                continue;
            }
            $contactBadge = reset($contactBadges);

            $pdf = $this->generateBadgeToPDF($contactBadge->getId(), $contact->getId(), null, null, $pdf);
        }
        $filename = 'custom_pdf_'.time().'.pdf';
        $pdf->Output($filename, 'I');

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
        $pdf = $this->getPDF($badgeId, $leadId, $hash, $isAdmin);
        echo $pdf->Output('custom_pdf_'.time().'.pdf', 'I');
        exit;
    }

    public function getPDF($badgeId, $leadId, $hash = null, $isAdmin = false)
    {
        $pdf = $this->loadFpdi();

        return $this->generateBadgeToPDF($badgeId, $leadId, $hash, $isAdmin, $pdf);
    }

    /**
     * @param      $badgeId
     * @param      $leadId
     * @param null $hash
     * @param bool $isAdmin
     *
     * @throws EntityNotFoundException
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    private function generateBadgeToPDF($badgeId, $leadId, $hash = null, $isAdmin = false, Fpdi &$pdf)
    {
        if (!$badge = $this->badgeModel->getEntity($badgeId)) {
            throw new EntityNotFoundException(sprintf('Badge with ID "%s" not exist', $badgeId));
        }
        $this->badge   = $badge;
        $this->contact = !empty($leadId) ? $this->leadModel->getEntity($leadId) : null;

        if ($this->contact && !$isAdmin) {
            $this->displayBadge($this->contact, $badge);
        }

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetMargins(0, 0, 0);

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
            $rtl       = ArrayHelper::getValue('rtl', $badge->getProperties()['text'.$i], false);

            $color      = ArrayHelper::getValue('color', $badge->getProperties()['text'.$i], '000000');
            $fontSize   = ArrayHelper::getValue('fontSize', $badge->getProperties()['text'.$i], 30);
            $lineHeight = ArrayHelper::getValue('lineHeight', $badge->getProperties()['text'.$i], 1);
            $stretch    = ArrayHelper::getValue('stretch', $badge->getProperties()['text'.$i], 0);
            $style      = ArrayHelper::getValue('style', $badge->getProperties()['text'.$i], []);
            $font       = ArrayHelper::getValue('font', $badge->getProperties()['text'.$i], $this->fontName);
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
            $pdf->MultiCell(
                $width - $positionX,
                '',
                $this->getCustomText('text'.$i),
                0,
                $align,
                false,
                1,
                $positionX,
                $positionY,
                true,
                $stretch
            );
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

            $avatar = ArrayHelper::getValue('avatar', $badge->getProperties()['image'.$i], false);
            $field  = ArrayHelper::getValue('fields', $badge->getProperties()['image'.$i], false);
            if (empty($field) && empty($avatar)) {
                continue;
            }

            $positionY = ArrayHelper::getValue('position', $badge->getProperties()['image'.$i], 0);
            $positionX = ArrayHelper::getValue('positionX', $badge->getProperties()['image'.$i], 0);
            $width     = ArrayHelper::getValue('width', $badge->getProperties()['image'.$i], 100);
            $height    = ArrayHelper::getValue('height', $badge->getProperties()['image'.$i], 100);
            $align     = ArrayHelper::getValue('align', $badge->getProperties()['image'.$i], 'C');
            $rounded   = ArrayHelper::getValue('rounded', $badge->getProperties()['image'.$i], false);
            if ($align !== 'C') {
                $align = '';
            }

            // reset position
            //  $pdf->SetXY($positionX, $positionY);
            $image = '';
            if ($hash) {
                if (!empty($avatar)) {
                    $image = $this->getAvatar();
                } else {
                    $image = $this->getCustomImage('image'.$i);
                }
            } else {
                $image = $this->getCustomImage('image'.$i, 'https://placehold.co/300x300.png');
            }

            if ($rounded) {
                $image = $this->badgeUrlGenerator->getLinkToRoundedImage($image, $width);
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
                    true,
                    150,
                    $align,
                    false,
                    false,
                    0,
                    'CT',
                    false,
                    false
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
        return $pdf;
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
        $image  = $this->getCustomTextFromFields($block, $default);
        $fields = $this->getFields($block);
        $field  = reset($fields);
        if ($this->contact && $field == 'country' && $image) {
            if ($flagImage = $this->assetsHelper->getCountryFlag($image, true)) {
                $image = $this->coreParametersHelper->getParameter('site_url').$flagImage;
            }
        }

        return $image;
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
        $fieldValue = $this->contact ? $this->contact->getFieldValue($alias) : null;

        return $this->contact ? $fieldValue : ($default ? $default : $alias);
    }

    /**
     * @param string $block
     *
     * @return string
     */
    private function getCustomTextFromFields($block, $default = null)
    {
        $fields = $this->getFields($block);
        $text   = [];
        foreach ($fields as $field) {
            $text[] = $this->getContactFieldValue($field, $default);
        }

        return implode(' ', $text);
    }

    /**
     * @param string $block
     *
     * @return array
     */
    private function getFields($block)
    {
        $fields = $this->badge->getProperties()[$block]['fields'];
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        return $fields;
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
        $segments    = ArrayHelper::getValue('segment', $restriction, []);
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

    public function
    Crop_ByRadius(
        $source_url,
        $Radius = "0px",
        $Keep_SourceFile = true
    ) {

        /*
            Output File is png, Because for crop we need transparent color

            if success :: this function returns url of Created File
            if Fial :: returns FALSE

            $Radius Input Examples ::
                                100     => 100px
                                100px   => 100px
                                50%     => 50%
        */

        if ($Radius == null) {
            return false;
        }


        $ImageInfo = getimagesize($source_url);
        $w         = $ImageInfo[0];
        $h         = $ImageInfo[1];
        $mime      = $ImageInfo['mime'];

        if ($mime != "image/jpeg" && $mime != "image/jpg" && $mime != "image/png") {
            return false;
        }

        if (strpos($Radius, "%") !== false) {
            //$Radius by Cent
            $Radius        = intval(str_replace("%", "", $Radius));
            $Smallest_Side = $w <= $h ? $w : $h;
            $Radius        = $Smallest_Side * $Radius / 100;

        } else {
            $Radius = strtolower($Radius);
            $Radius = str_replace("px", "", $Radius);
        }

        $Radius = is_numeric($Radius) ? intval($Radius) : 0;

        if ($Radius == 0) {
            return false;
        }
        $src    = imagecreatefromstring(file_get_contents($source_url));
        $newpic = imagecreatetruecolor($w, $h);
        imagealphablending($newpic, false);
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
        //$transparent = imagecolorallocatealpha($newpic, 255, 0, 0, 0);//RED For Test

        $r = $Radius / 2;

        /********************** Pixel step config ********************************/

        $Pixel_Step_def = 0.4;//smaller step take longer time! if set $Pixel_Step=0.1 result is better than  $Pixel_Step=1 but it take longer time!

        //We select the pixels we are sure are in range, to Take up the bigger steps and shorten the processing time

        $Sure_x_Start = $Radius + 1;
        $Sure_x_End   = $w - $Radius - 1;
        $Sure_y_Start = $Radius + 1;
        $Sure_y_End   = $h - $Radius - 1;
        if ($w <= $h) {
            //We want to use the larger side to make processing shorter
            $Use_x_Sure = false;
            $Use_y_Sure = true;
        } else {
            $Use_x_Sure = true;
            $Use_y_Sure = false;
        }
        /********************** Pixel step config END********************************/

        $Pixel_Step = $Pixel_Step_def;
        for ($x = 0; $x < $w; $x += $Pixel_Step) {

            if ($Use_x_Sure && $x > $Sure_x_Start && $x < $Sure_x_End) {
                $Pixel_Step = 1;
            } else {
                $Pixel_Step = $Pixel_Step_def;
            }

            for ($y = 0; $y < $h; $y += $Pixel_Step) {
                if ($Use_y_Sure && $y > $Sure_y_Start && $y < $Sure_y_End) {
                    $Pixel_Step = 1;
                } else {
                    $Pixel_Step = $Pixel_Step_def;
                }

                $c = imagecolorat($src, $x, $y);

                $_x           = ($x - $Radius) / 2;
                $_y           = ($y - $Radius) / 2;
                $Inner_Circle = ((($_x * $_x) + ($_y * $_y)) < ($r * $r));
                $top_Left     = ($x > $Radius || $y > $Radius) || $Inner_Circle;

                $_x           = ($x - $Radius) / 2 - ($w / 2 - $Radius);
                $_y           = ($y - $Radius) / 2;
                $Inner_Circle = ((($_x * $_x) + ($_y * $_y)) < ($r * $r));
                $top_Right    = ($x < ($w - $Radius) || $y > $Radius) || $Inner_Circle;

                $_x           = ($x - $Radius) / 2;
                $_y           = ($y - $Radius) / 2 - ($h / 2 - $Radius);
                $Inner_Circle = ((($_x * $_x) + ($_y * $_y)) < ($r * $r));
                $Bottom_Left  = ($x > $Radius || $y < ($h - $Radius)) || $Inner_Circle;

                $_x           = ($x - $Radius) / 2 - ($w / 2 - $Radius);
                $_y           = ($y - $Radius) / 2 - ($h / 2 - $Radius);
                $Inner_Circle = ((($_x * $_x) + ($_y * $_y)) < ($r * $r));
                $Bottom_Right = ($x < ($w - $Radius) || $y < ($h - $Radius)) || $Inner_Circle;

                if ($top_Left && $top_Right && $Bottom_Left && $Bottom_Right) {

                    imagesetpixel($newpic, $x, $y, $c);

                } else {
                    imagesetpixel($newpic, $x, $y, $transparent);
                }

            }
        }


        imagesavealpha($newpic, true);
        header('Content-type: image/png');
        imagepng($newpic);
        imagedestroy($newpic);
        imagedestroy($src);

    }

    //resize and crop image by center
    function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80)
    {
        $imgsize = getimagesize($source_file);
        $width   = $imgsize[0];
        $height  = $imgsize[1];
        $mime    = $imgsize['mime'];

        switch ($mime) {
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image        = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image        = "imagepng";
                $quality      = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image        = "imagejpeg";
                $quality      = 80;
                break;

            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new  = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) {
            imagedestroy($dst_img);
        }
        if ($src_img) {
            imagedestroy($src_img);
        }
    }
}