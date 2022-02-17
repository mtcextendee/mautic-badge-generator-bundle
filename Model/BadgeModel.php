<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticBadgeGeneratorBundle\Model;

use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\MauticBadgeGeneratorBundle\BadgeEvents;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\Badge;
use MauticPlugin\MauticBadgeGeneratorBundle\Entity\BadgeRepository;
use MauticPlugin\MauticBadgeGeneratorBundle\Event\BadgeEvent;
use MauticPlugin\MauticBadgeGeneratorBundle\Form\Type\BadgeType;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class BadgeModel extends FormModel implements AjaxLookupModelInterface
{
    /**
     * Retrieve the permissions base.
     *
     * @return string
     */
    public function getPermissionBase()
    {
        return 'lead:leads';
    }

    /**
     * {@inheritdoc}
     *
     * @return BadgeRepository
     */
    public function getRepository()
    {
        /** @var BadgeRepository $repo */
        $repo = $this->em->getRepository('MauticBadgeGeneratorBundle:Badge');

        $repo->setTranslator($this->translator);

        return $repo;
    }

    /**
     * Here just so PHPStorm calms down about type hinting.
     *
     * @param null $id
     *
     * @return Badge|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new Badge();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Badge) {
            throw new \InvalidArgumentException('Entity must be of class Badge');
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(BadgeType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $entity
     * @param $isNew
     * @param $event
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, \Symfony\Component\EventDispatcher\Event $event = null)
    {
        if (!$entity instanceof Badge) {
            throw new MethodNotAllowedHttpException(['Badge']);
        }

        switch ($action) {
            case 'pre_save':
                $name = BadgeEvents::PRE_SAVE;
                break;
            case 'post_save':
                $name = BadgeEvents::POST_SAVE;
                break;
            case 'pre_delete':
                $name = BadgeEvents::PRE_DELETE;
                break;
            case 'post_delete':
                $name = BadgeEvents::POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new BadgeEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * @param        $type
     * @param string $filter
     * @param int    $limit
     * @param int    $start
     * @param array  $options
     */
    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        switch ($type) {
            case 'badge':
                break;
        }

        return $results;
    }
}
