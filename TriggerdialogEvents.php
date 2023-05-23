<?php

namespace MauticPlugin\MauticTriggerdialogBundle;

final class TriggerdialogEvents
{
    /**
     * The mautic.triggercampaign_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent instance.
     *
     * @var string
     */
    public const TRIGGER_CAMPAIGN_PRE_SAVE = 'mautic.triggercampaign_pre_save';

    /**
     * The mautic.triggercampaign_post_save is thrown right after a form is persisted.
     *
     * The event listener receives a MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent instance.
     *
     * @var string
     */
    public const TRIGGER_CAMPAIGN_POST_SAVE = 'mautic.triggercampaign_post_save';

    /**
     * Themautic.triggercampaign_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent instance.
     *
     * @var string
     */
    public const TRIGGER_CAMPAIGN_PRE_DELETE = 'mautic.triggercampaign_pre_delete';

    /**
     * The mautic.triggercampaign_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent instance.
     *
     * @var string
     */
    public const TRIGGER_CAMPAIGN_POST_DELETE = 'mautic.triggercampaign_post_delete';

    /**
     * The mautic.triggerdialog.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a Mautic\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mautic.triggerdialog.on_campaign_trigger_action';
}
