<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;

class TriggerCampaignEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(TriggerCampaign &$triggerCampaign, $isNew = false)
    {
        $this->entity = &$triggerCampaign;
        $this->isNew = $isNew;
    }

    /**
     * Returns the TriggerCampaign entity.
     */
    public function getTriggerCampaign(): TriggerCampaign
    {
        return $this->entity;
    }
}
