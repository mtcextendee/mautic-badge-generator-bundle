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
    private \Mautic\CoreBundle\Helper\FileUploader $fileUploader;

    private \Mautic\CoreBundle\Helper\CoreParametersHelper $coreParametersHelper;

    private array $uploadFilesName = ['source', 'ttf_upload'];

    private string $suffixForUpload = '_upload';

    private \Mautic\CoreBundle\Helper\PathsHelper $pathsHelper;

    /**
     * BadgeUploader constructor.
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
     * @return mixed
     *
     * @throws \Mautic\CoreBundle\Exception\FileUploadException
     */
    public function uploadPropertiesFiles(Badge $entity, Request $request)
    {
        $uploadDir  = $this->getUploadDir($entity);
        $badge      = ArrayHelper::getValue('badge', $request->files->all(), []);
        $properties = ArrayHelper::getValue('properties', $badge, []);
        $files      = [];
        foreach ($properties as $key => $files) {
            foreach ($this->getUploadFilesName() as $fileName) {
                /* @var UploadedFile $file */
                if (empty($files[$fileName])) {
                    continue;
                }
                $file = $files[$fileName];
                try {
                    $uploadedFile                                                         = $this->fileUploader->upload($uploadDir, $file);
                    $properties                                                           = $this->getEntityVar($entity, 'properties');
                    $properties[$key][str_replace($this->suffixForUpload, '', $fileName)] = $uploadedFile;
                    $this->getEntityVar($entity, 'properties', 'set', $properties);
                } catch (FileUploadException $e) {
                }
            }
        }

        return $files;
    }

    /**
     * @throws \Mautic\CoreBundle\Exception\FileUploadException
     */
    public function uploadFiles(Badge $entity, Request $request, Form $form): void
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
     * @param string $fileName
     */
    public function getCompleteFilePath(Badge $entity, $fileName): string
    {
        $uploadDir = $this->getUploadDir($entity);

        return $uploadDir.$fileName;
    }

    public function deleteAllFilesOfBadge(Badge $entity): void
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
        if ('get' == $action) {
            return $entity->$var();
        } else {
            $entity->$var($value);
        }
    }

    private function getUploadDir(): string
    {
        return $this->getBadgeImagePath(true);
    }

    /**
     * @param bool $fullPath
     */
    private function getBadgeImagePath($fullPath = false): string
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

    public function getUploadFilesName(): array
    {
        return $this->uploadFilesName;
    }

    /**
     * @param string $pattern
     */
    public function getUploadedFiles($pattern = '*'): \Symfony\Component\Finder\Finder
    {
        $path   = $this->getBadgeImagePath(true);
        $finder = new Finder();

        return $finder->files()->name($pattern)->in($path);
    }
}
