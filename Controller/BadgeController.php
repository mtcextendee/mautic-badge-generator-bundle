<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Mautic\CoreBundle\Helper\ArrayHelper;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Generator\BadgeGenerator;
use MauticPlugin\MauticBadgeGeneratorBundle\Token\BadgeHashGenerator;
use MauticPlugin\MauticBadgeGeneratorBundle\Uploader\BadgeUploader;
use Symfony\Component\Form\Form;

class BadgeController extends AbstractStandardFormController
{
    /**
     * @var string
     */
    private $source;

    /**
     * {@inheritdoc}
     */
    protected function getJsLoadMethodPrefix()
    {
        return 'badge.badge';
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return 'badge.badge';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteBase()
    {
        return 'badge_generator';
    }

    /***
     * @param null $objectId
     *
     * @return string
     */
    protected function getSessionBase($objectId = null)
    {
        return 'badgeGenerator'.(($objectId) ? '.'.$objectId : '');
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'MauticBadgeGeneratorBundle:Badge';
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        return $this->batchDeleteStandard();
    }

    /**
     * @param $objectId
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cloneAction($objectId)
    {
        return $this->cloneStandard($objectId);
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction($objectId, $ignorePost = false)
    {
        return parent::editStandard($objectId, $ignorePost);
    }

    /**
     * @param Badge $badge
     * @param Badge $oldBadge
     *
     * @return array|void
     */
    protected function afterEntityClone($badge, $oldBadge)
    {
        $this->get('session')->set('clonedSource', $oldBadge->getSource());
    }

    /**
     * @param Badge    $entity
     * @param Form     $form
     * @param          $action
     * @param null     $objectId
     * @param bool     $isClone
     *
     * @return bool
     */
    protected function beforeEntitySave($entity, Form $form, $action, $objectId = null, $isClone = false)
    {
        /** @var BadgeUploader $uploader */
        $uploader = $this->get('mautic.badge.uploader');
        $uploader->uploadFiles($entity, $this->request, $form);
        $uploader->uploadPropertiesFiles($entity, $this->request);

        if ($isClone && !$entity->getSource()) {
            if ($this->get('session')->has('clonedSource')) {
                $entity->setSource($this->get('session')->get('clonedSource'));
            }
        }

        return true;
    }


    /**
     * @param int $page
     *
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction($page = 1)
    {
        return $this->indexStandard($page);
    }

    /**
     * @return \Mautic\CoreBundle\Controller\Response|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function newAction()
    {
        return $this->newStandard();
    }

    /**
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        //set the page we came from
        $page      = 1;
        $returnUrl = $this->generateUrl('mautic_badge_generator_index', ['page' => $page]);

        return $this->postActionRedirect(
            [
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'MauticBadgeGeneratorBundle:Badge:index',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_badge_generator_index',
                    'mauticContent' => 'badge',
                ],
            ]
        );
    }

    /**
     * @param $args
     * @param $action
     *
     * @return mixed
     */
    protected function getViewArguments(array $args, $action)
    {
        $viewParameters = [];

        switch ($action) {
            case 'new':
            case 'edit':
                if ($integration = $this->get('mautic.helper.integration')->getIntegrationObject('BadgeGenerator')) {
                    $integrationSettings                   = $integration->mergeConfigToFeatureSettings();
                    $viewParameters['numberOfTextBlock']   = ArrayHelper::getValue(
                        'numberOfTextBlocks',
                        $integrationSettings,
                        BadgeGenerator::NUMBER_OF_DEFAULT_TEXT_BLOCKS
                    );
                    $viewParameters['numberOfImagesBlock'] = ArrayHelper::getValue(
                        'numberOfImagesBlocks',
                        $integrationSettings,
                        BadgeGenerator::NUMBER_OF_DEFAULT_IMAGES_BLOCKS
                    );
                }
            case 'index':
            case 'edit':
                $viewParameters['uploader'] = $this->get('mautic.badge.uploader');
                break;

        }

        $args['viewParameters'] = array_merge($args['viewParameters'], $viewParameters);

        return $args;
    }

    /**
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function deleteAction($objectId)
    {
        return $this->deleteStandard($objectId);
    }

    /**
     * @param      $objectId
     * @param null $contactId
     */
    public function generateAction($objectId, $contactId = null, $hash = null)
    {
        /** @var BadgeGenerator $badgeGenerator */
        $badgeGenerator = $this->get('mautic.badge.generator');
        /** @var BadgeHashGenerator $hashGenerator */
        $hashGenerator = $this->get('mautic.badge.hash.generator');
        if (!$hashGenerator->isValidHash($contactId, $hash)) {
            return $this->accessDenied();

        }

        return $badgeGenerator->generate($objectId, $contactId, $hash);
    }

}
