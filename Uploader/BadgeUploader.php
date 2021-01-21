<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Uploader;

use Mautic\CoreBundle\Helper\ArrayHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\FileUploader;
use Mautic\CoreBundle\Helper\PathsHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class BadgeUploader
{
    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var array
     */
    private $uploadFilesName = ['source', 'ttf_upload'];

    private $suffixForUpload = '_upload';

    /**
     * @var PathsHelper
     */
    private $pathsHelper;

    /**
     * BadgeUploader constructor.
     *
     * @param FileUploader         $fileUploader
     * @param CoreParametersHelper $coreParametersHelper
     * @param PathsHelper          $pathsHelper
     */
    public function __construct(
        FileUploader $fileUploader,
        CoreParametersHelper $coreParametersHelper,
        PathsHelper $pathsHelper
    ) {
        $this->fileUploader         = $fileUploader;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->pathsHelper          = $pathsHelper;
    }

    /**
     * @param Badge   $entity
     * @param Request $request
     *
     * @return mixed
     * @throws \Mautic\CoreBundle\Exception\FileUploadException
     */
    public function uploadPropertiesFiles(Badge $entity, Request $request)
    {
        $uploadDir  = $this->getUploadDir($entity);
        $badge      = ArrayHelper::getValue('badge', $request->files->all(), []);
        $properties = ArrayHelper::getValue('properties', $badge, []);
        $files = [];
        foreach ($properties as $key => $files) {
            foreach ($this->getUploadFilesName() as $fileName) {
                /* @var UploadedFile $file */
                if (empty($files[$fileName])) {
                    continue;
                }
                $file = $files[$fileName];
                try {
                    $uploadedFile            = $this->fileUploader->upload($uploadDir, $file);
                    $properties              = $this->getEntityVar($entity, 'properties');
                    $properties[$key][str_replace($this->suffixForUpload, '', $fileName)] = $uploadedFile;
                    $this->getEntityVar($entity, 'properties', 'set', $properties);
                } catch (FileUploadException $e) {
                }
            }
        }

        return $files;
    }

    /**
     * @param Badge   $entity
     * @param Request $request
     * @param Form    $form
     *
     * @throws \Mautic\CoreBundle\Exception\FileUploadException
     */
    public function uploadFiles(Badge $entity, Request $request, Form $form)
    {
        $files = [];
        if (isset($request->files->all()['badge'])) {
            $files = $request->files->all()['badge'];
        }

        $uploadDir = $this->getUploadDir($entity);
        foreach ($this->getUploadFilesName() as $fileName) {

            /* @var UploadedFile $file */
            if (empty($files[$fileName])) {
                continue;
            }
            $file = $files[$fileName];

            try {
                $uploadedFile = $this->fileUploader->upload($uploadDir, $file);
                $this->getEntityVar($entity, $fileName, 'set', $uploadedFile);
            } catch (FileUploadException $e) {
            }
        }
    }

    /**
     * @param Badge  $entity
     * @param string $fileName
     *
     * @return string
     */
    public function getCompleteFilePath(Badge $entity, $fileName)
    {
        $uploadDir = $this->getUploadDir($entity);

        return $uploadDir.$fileName;
    }

    /**
     * @param Badge $entity
     */
    public function deleteAllFilesOfBadge(Badge $entity)
    {
        $uploadDir = $this->getUploadDir($entity);
        $this->fileUploader->delete($uploadDir);
    }

    /**
     * @param $entity
     *
     * @return string
     */
    public function getFullUrl($entity, $key)
    {
        if ($fileName = $this->getEntityVar($entity, $key)) {
            return $this->coreParametersHelper->getParameter(
                    'site_url'
                ).DIRECTORY_SEPARATOR.$this->getBadgeImagePath().$fileName;
        }
    }

    /**
     * @param object $entity
     * @param string $key
     * @param string $action
     */
    public function getEntityVar($entity, $key, $action = 'get', $value = '')
    {
        $var = $action.ucfirst($key);
        if ($action == 'get') {
            return $entity->$var();
        } else {
            $entity->$var($value);
        }
    }

    /**
     * @param Badge $entity
     *
     * @return string
     */
    private function getUploadDir(Badge $entity)
    {
        return $this->getBadgeImagePath(true);
    }

    /**
     * @param bool $fullPath
     *
     * @return string
     */
    private function getBadgeImagePath($fullPath = false)
    {
        $imagesAbsoluteDirectory = $this->pathsHelper->getSystemPath(
            'images',
            true
        );
        $badgesDirectory         = DIRECTORY_SEPARATOR.$this->coreParametersHelper->getParameter(
                'badge_image_directory'
            ).DIRECTORY_SEPARATOR;

        if (!file_exists($imagesAbsoluteDirectory.$badgesDirectory)) {
            mkdir($imagesAbsoluteDirectory.$badgesDirectory);
        }

        return $this->pathsHelper->getSystemPath(
                'images',
                $fullPath
            ).$badgesDirectory;
    }

    /**
     * @return array
     */
    public function getUploadFilesName()
    {
        return $this->uploadFilesName;
    }

    /**
     * @param string $pattern
     *
     * @return Finder
     */
    public function getUploadedFiles($pattern = '*')
    {
        $path = $this->getBadgeImagePath(true);
        $finder = new Finder();
        return $finder->files()->name($pattern)->in($path);

    }


    /**
     * @param string $string
     * @param string $prefix
     *
     * @return bool
     */
    private function startWith($string, $prefix)
    {
        return substr($string, 0, strlen($prefix)) == $prefix;
    }
}