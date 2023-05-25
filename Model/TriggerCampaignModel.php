<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Entity\TriggerCampaign;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Entity\TriggerCampaignRepository;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\TriggerCampaignType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\TriggerdialogEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class TriggerCampaignModel extends FormModel
{
    public const NAME = 'triggerdialog.campaign';

    /**
     * {@inheritDoc}
     *
     * @return TriggerCampaignRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('LeuchtfeuerPrintmailingBundle:TriggerCampaign');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'triggerdialog:campaigns';
    }

    /**
     * {@inheritdoc}
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof TriggerCampaign) {
            throw new MethodNotAllowedHttpException(['TriggerCampaign']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(TriggerCampaignType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @return TriggerCampaign
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new TriggerCampaign();
        }

        /** @var TriggerCampaign $entity */
        $entity = parent::getEntity($id);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof TriggerCampaign) {
            throw new MethodNotAllowedHttpException(['TriggerCampaign']);
        }

        switch ($action) {
            case 'pre_save':
                $name = TriggerdialogEvents::TRIGGER_CAMPAIGN_PRE_SAVE;
                break;
            case 'post_save':
                $name = TriggerdialogEvents::TRIGGER_CAMPAIGN_POST_SAVE;
                break;
            case 'pre_delete':
                $name = TriggerdialogEvents::TRIGGER_CAMPAIGN_PRE_DELETE;
                break;
            case 'post_delete':
                $name = TriggerdialogEvents::TRIGGER_CAMPAIGN_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new TriggerCampaignEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }
}
