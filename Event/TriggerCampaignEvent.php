<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;

class TriggerCampaignEvent extends CommonEvent
{
    /**
     * TriggerCampaignEvent constructor.
     *
     * @param TriggerCampaign $triggerCampaign
     * @param bool            $isNew
     */
    public function __construct(TriggerCampaign &$triggerCampaign, $isNew = false)
    {
        $this->entity = &$triggerCampaign;
        $this->isNew = $isNew;
    }

    /**
     * Returns the TriggerCampaign entity.
     *
     * @return TriggerCampaign
     */
    public function getTriggerCampaign()
    {
        return $this->entity;
    }
}
