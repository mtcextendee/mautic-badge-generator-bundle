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

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\PathsHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\LeadBundle\Entity\Lead;

class RoundedImageGenerator
{

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var PathsHelper
     */
    private $pathsHelper;


    /**
     * BadgeHashGenerator constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param PathsHelper          $pathsHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, PathsHelper $pathsHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->pathsHelper = $pathsHelper;

        $this->pathToDirectory = $this->pathsHelper->getSystemPath('images', true). '/'.$this->coreParametersHelper->getParameter('rounded_image_directory');
    }

    /**
     * @param string $image
     * @param string $width
     */
    public function generate($image, $width)
    {
        $pathToImage = $this->getPathToImage(md5($image.$width));
        if (!file_exists($pathToImage)) {
            $this->resizeCropImage($width*2, $width*2, $image, $pathToImage);
        }
        $this->CropByRadius($pathToImage, "50%");
    }

    /**
     * @param $image
     *
     * @return string
     */
    private function getPathToImage($md5ofImage)
    {
        return $this->getPathToDirectory().'/'.$md5ofImage.'png';
    }

    /**
     * @return string
     */
    private function getPathToDirectory()
    {
        $path = $this->pathsHelper->getSystemPath('images', true). '/'.$this->coreParametersHelper->getParameter('rounded_image_directory');
        if (!file_exists($path)) {
            mkdir($path);
        }
        return $path;
    }

    private function  CropByRadius($source_url,$Radius="0px" ,$Keep_SourceFile = TRUE){

        if( $Radius == NULL )
            return FALSE;




        $ImageInfo = getimagesize($source_url);
        $w = $ImageInfo[0];
        $h = $ImageInfo[1];
        $mime = $ImageInfo['mime'];

        if( $mime != "image/jpeg" && $mime != "image/jpg" && $mime != "image/png")
            return FALSE;

        if( strpos($Radius,"%") !== FALSE ){
            //$Radius by Cent
            $Radius = intval( str_replace("%","",$Radius) );
            $Smallest_Side = $w <= $h ? $w : $h;
            $Radius = $Smallest_Side * $Radius / 100;

        }else{
            $Radius = strtolower($Radius);
            $Radius = str_replace("px","",$Radius);
        }

        $Radius = is_numeric($Radius) ? intval($Radius) : 0;

        if( $Radius == 0 ) return FALSE;
        $src = imagecreatefromstring(file_get_contents($source_url));
        $newpic = imagecreatetruecolor($w,$h);
        imagealphablending($newpic,false);
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
        //$transparent = imagecolorallocatealpha($newpic, 255, 0, 0, 0);//RED For Test

        $r = $Radius / 2;

        /********************** Pixel step config ********************************/

        $Pixel_Step_def = 0.4;//smaller step take longer time! if set $Pixel_Step=0.1 result is better than  $Pixel_Step=1 but it take longer time!

        //We select the pixels we are sure are in range, to Take up the bigger steps and shorten the processing time

        $Sure_x_Start = $Radius +1;
        $Sure_x_End = $w - $Radius -1;
        $Sure_y_Start = $Radius +1;
        $Sure_y_End = $h - $Radius -1;
        if( $w <= $h ){
            //We want to use the larger side to make processing shorter
            $Use_x_Sure = FALSE;
            $Use_y_Sure = TRUE;
        }else{
            $Use_x_Sure = TRUE;
            $Use_y_Sure = FALSE;
        }
        /********************** Pixel step config END********************************/

        $Pixel_Step = $Pixel_Step_def;
        for( $x=0; $x < $w ; $x+=$Pixel_Step ){

            if( $Use_x_Sure && $x > $Sure_x_Start && $x < $Sure_x_End ) $Pixel_Step = 1;else $Pixel_Step = $Pixel_Step_def;

            for( $y=0; $y < $h ; $y+=$Pixel_Step){
                if( $Use_y_Sure && $y > $Sure_y_Start && $y < $Sure_y_End ) $Pixel_Step = 1;else $Pixel_Step = $Pixel_Step_def;

                $c = imagecolorat($src,$x,$y);

                $_x = ($x - $Radius) /2;
                $_y = ($y - $Radius) /2;
                $Inner_Circle = ( ( ($_x*$_x) + ($_y*$_y) ) < ($r*$r) );
                $top_Left = ($x > $Radius || $y > $Radius) || $Inner_Circle;

                $_x = ($x - $Radius) /2 - ($w/2 - $Radius);
                $_y = ($y - $Radius) /2;
                $Inner_Circle = ( ( ($_x*$_x) + ($_y*$_y) ) < ($r*$r) );
                $top_Right = ($x < ($w - $Radius) || $y > $Radius) || $Inner_Circle;

                $_x = ($x - $Radius) /2;
                $_y = ($y - $Radius) /2 - ($h/2 - $Radius);
                $Inner_Circle = ( ( ($_x*$_x) + ($_y*$_y) ) < ($r*$r) );
                $Bottom_Left =  ($x > $Radius || $y < ($h - $Radius) ) || $Inner_Circle;

                $_x = ($x - $Radius) /2 - ($w/2 - $Radius);
                $_y = ($y - $Radius) /2 - ($h/2 - $Radius);
                $Inner_Circle = ( ( ($_x*$_x) + ($_y*$_y) ) < ($r*$r) );
                $Bottom_Right = ($x < ($w - $Radius) || $y < ($h - $Radius) ) || $Inner_Circle;

                if($top_Left && $top_Right && $Bottom_Left && $Bottom_Right ){

                    imagesetpixel($newpic,$x,$y,$c);

                }else{
                    imagesetpixel($newpic,$x,$y,$transparent);
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
    private function resizeCropImage($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];

        switch($mime){
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;

            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($width_new > $width){
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        }else{
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if($dst_img)imagedestroy($dst_img);
        if($src_img)imagedestroy($src_img);
    }


}
