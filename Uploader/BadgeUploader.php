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

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\FileUploader;
use Mautic\CoreBundle\Helper\PathsHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use Symfony\Component\Form\Form;

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
    private $uploadFilesName = ['source'];

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
     * @param Badge $entity
     * @param              $request
     * @param Form         $form
     */
    public function uploadFiles(Badge $entity, $request, Form $form)
    {
        $files = [];
        if (isset($request->files->all()['badge'])) {
            $files = $request->files->all()['badge'];
        }

        foreach ($this->getUploadFilesName() as $fileName) {
            $uploadDir = $this->getUploadDir($entity);


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
     * @param Badge $entity
     * @param string       $fileName
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
            $entity->$var((string) $value);
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
        return $this->pathsHelper->getSystemPath(
            'images',
            $fullPath
        ).DIRECTORY_SEPARATOR.$this->coreParametersHelper->getParameter(
            'badge_image_directory'
        ).DIRECTORY_SEPARATOR;
    }

    /**
     * @return array
     */
    public function getUploadFilesName()
    {
        return $this->uploadFilesName;
    }
}