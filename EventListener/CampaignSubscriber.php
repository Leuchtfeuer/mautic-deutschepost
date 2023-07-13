<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Entity\TriggerCampaign;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Event\TriggerCampaignEvent;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\ActionType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Model\TriggerCampaignModel;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Service\PrintmailingService;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\PrintmailingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CampaignSubscriber implements EventSubscriberInterface
{
    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var AuditLogModel
     */
    protected $auditLogModel;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    /**
     * @var TriggerCampaignModel
     */
    protected $triggerCampaignModel;

    /**
     * CampaignSubscriber constructor.
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, TriggerCampaignModel $triggerCampaignModel)
    {
        $this->ipLookupHelper       = $ipLookupHelper;
        $this->auditLogModel        = $auditLogModel;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->triggerCampaignModel = $triggerCampaignModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD                => ['onCampaignBuild', 0],
            PrintmailingEvents::TRIGGER_CAMPAIGN_PRE_SAVE   => ['onTriggerCampaignPreSave', 0],
            PrintmailingEvents::TRIGGER_CAMPAIGN_POST_SAVE  => ['onTriggerCampaignPostSave', 0],
            PrintmailingEvents::TRIGGER_CAMPAIGN_PRE_DELETE => ['onTriggerCampaignPreDelete', 0],
            PrintmailingEvents::ON_CAMPAIGN_TRIGGER_ACTION  => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addAction(
            'plugin.printmailing.campaign',
            [
                'eventName'   => PrintmailingEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'label'       => 'plugin.printmailing.campaign.label',
                'description' => 'plugin.printmailing.campaign.description',
                'formType'    => ActionType::class,
            ]
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MauticPlugin\LeuchtfeuerPrintmailingBundle\Exception\RequestException
     */
    public function onTriggerCampaignPreSave(TriggerCampaignEvent $event): void
    {
        $triggerCampaign = $event->getTriggerCampaign();

        if ($triggerCampaign->isNew()) {
            $printNodeId = time();
            $triggerCampaign->setPrintNodeId('ID_'.$printNodeId);
            $triggerCampaign->setPrintNodeDescription('DESC_'.$printNodeId);
        } elseif ($changes = $event->getChanges()) {
            if (isset($changes['name']) || isset($changes['startDate'])) {
                $this->getPrintmailingService()->updateCampaign($triggerCampaign);
            }
            if (isset($changes['variables'])) {
                $this->getPrintmailingService()->updateCampaignVariable($triggerCampaign, $changes['variables'][1]);
            }
        }
    }

    public function onTriggerCampaignPostSave(TriggerCampaignEvent $event): void
    {
        $triggerCampaign = $event->getTriggerCampaign();

        if (isset($triggerCampaign->getChanges()['printNodeId'])) {
            $triggerCampaign = $this->getPrintmailingService()->createCampaign($triggerCampaign);
            $this->triggerCampaignModel->getRepository()->saveEntity($triggerCampaign);
        }

        if ($details = $event->getChanges()) {
            $this->auditLogModel->writeToLog([
                'bundle'    => 'printmailing',
                'object'    => TriggerCampaignModel::NAME,
                'objectId'  => $event->getTriggercampaign()->getId(),
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ]);
        }
    }

    public function onTriggerCampaignPreDelete(TriggerCampaignEvent $event): void
    {
        // here you can the API call if there is a possibility to remove/hide mailing within Deutschepost API.
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        if (false === $event->checkContext('plugin.printmailing.campaign')) {
            return;
        }

        $config = $event->getConfig();
        if (!isset($config['trigger_campaign']) || empty($config['trigger_campaign'])) {
            $event->setFailed('No trigger campaign found for given ID.');

            return;
        }

        $triggerCampaign = $this->triggerCampaignModel->getEntity($config['trigger_campaign']);
        if (!$triggerCampaign instanceof TriggerCampaign) {
            $event->setFailed('Could not find matching campaign ID.');

            return;
        }

        try {
            $lead = $event->getLead();
            $this->auditLogModel->writeToLog([
                'bundle'    => 'printmailing',
                'object'    => 'lead',
                'objectId'  => $lead->getId(),
                'action'    => 'registered for campaign',
                'details'   => $event->getEventSettings(),
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ]);
            $this->getPrintmailingService()->createRecipient($triggerCampaign, $lead);
            $event->setResult(true);
        } catch (\Exception $exception) {
            $event->setFailed($exception->getMessage());
        }
    }

    protected function getPrintmailingService(): PrintmailingService
    {
        return PrintmailingService::makeInstance(
            (int) $this->coreParametersHelper->get('printmailing_masId'),
            $this->coreParametersHelper->get('printmailing_masClientId'),
            $this->coreParametersHelper->get('printmailing_rest_password')
        );
    }
}
