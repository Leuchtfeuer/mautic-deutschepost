<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Entity\TriggerCampaign;

class TriggerCampaignEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(TriggerCampaign &$triggerCampaign, $isNew = false)
    {
        $this->entity = &$triggerCampaign;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the TriggerCampaign entity.
     */
    public function getTriggerCampaign(): TriggerCampaign
    {
        return $this->entity;
    }
}
