<?php
namespace MauticPlugin\MauticTriggerdialogBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use MauticPlugin\MauticTriggerdialogBundle\Entity\TriggerCampaign;
use MauticPlugin\MauticTriggerdialogBundle\Event\TriggerCampaignEvent;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ActionType;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use MauticPlugin\MauticTriggerdialogBundle\Service\TriggerdialogService;
use MauticPlugin\MauticTriggerdialogBundle\TriggerdialogEvents;
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
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param IpLookupHelper       $ipLookupHelper
     * @param AuditLogModel        $auditLogModel
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, TriggerCampaignModel $triggerCampaignModel)
    {
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel = $auditLogModel;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->triggerCampaignModel = $triggerCampaignModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            TriggerdialogEvents::TRIGGER_CAMPAIGN_PRE_SAVE => ['onTriggerCampaignPreSave', 0],
            TriggerdialogEvents::TRIGGER_CAMPAIGN_POST_SAVE => ['onTriggerCampaignPostSave', 0],
            TriggerdialogEvents::TRIGGER_CAMPAIGN_PRE_DELETE => ['onTriggerCampaignPreDelete', 0],
            TriggerdialogEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addAction(
            'plugin.triggerdialog.campaign',
            [
                'eventName' => TriggerdialogEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'label' => 'plugin.triggerdialog.campaign.label',
                'description' => 'plugin.triggerdialog.campaign.description',
                'formType' => ActionType::class,
            ]
        );
    }

    /**
     * @param TriggerCampaignEvent $event
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MauticPlugin\MauticTriggerdialogBundle\Exception\RequestException
     */
    public function onTriggerCampaignPreSave(TriggerCampaignEvent $event): void
    {
        $triggerCampaign = $event->getTriggerCampaign();



        if ($triggerCampaign->isNew()) {
            $printNodeId = time();
            $triggerCampaign->setPrintNodeId('ID_' . $printNodeId);
            $triggerCampaign->setPrintNodeDescription('DESC_' . $printNodeId);
        } elseif ($changes = $event->getChanges()) {
            if (isset($changes['name']) || isset($changes['startDate'])) {
                $this->getTriggerDialogService()->updateCampaign($triggerCampaign);
            }
            if (isset($changes['variables'])) {
                $diff = array_diff($changes['variables'][0], $changes['variables'][1]);
                $origin = $changes['variables'][0];
                $diff = array_filter($changes['variables'][1], function($array) use ($origin) {
                    dump($array);
                    dump($origin);
                    foreach ($origin as $var) {
                        if($var === $array){
                            return false;
                        }
                    }
                    return true;
                }, ARRAY_FILTER_USE_BOTH);
                dump($diff);
                $this->getTriggerDialogService()->updateCampaignVariable($triggerCampaign, $changes['variables'][1]);
            }
        }

    }




    /**
     * @param TriggerCampaignEvent $event
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MauticPlugin\MauticTriggerdialogBundle\Exception\RequestException
     */
    public function onTriggerCampaignPostSave(TriggerCampaignEvent $event): void
    {
        $triggerCampaign = $event->getTriggerCampaign();


        if (isset($triggerCampaign->getChanges()['printNodeId'])) {
            $triggerCampaign = $this->getTriggerDialogService()->createCampaign($triggerCampaign);
            $tcmr = $this->triggerCampaignModel->getRepository();
            $tcmr->saveEntity($triggerCampaign);
        }

        if ($details = $event->getChanges()) {
            $this->auditLogModel->writeToLog([
                'bundle' => 'triggerdialog',
                'object' => TriggerCampaignModel::NAME,
                'objectId' => $event->getTriggercampaign()->getId(),
                'details' => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ]);
        }

    }

    /**
     * @param TriggerCampaignEvent $event
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MauticPlugin\MauticTriggerdialogBundle\Exception\RequestException
     */
    public function onTriggerCampaignPreDelete(TriggerCampaignEvent $event): void
    {
        $this->getTriggerDialogService()->updateCampaign($event->getTriggerCampaign(), 'finished');
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        if ($event->checkContext('plugin.triggerdialog.campaign') === false) {
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
            $this->getTriggerDialogService()->createCampaignTrigger($triggerCampaign, $lead);
            $event->setResult(true);
        } catch (\Exception $exception) {
            $event->setFailed($exception->getMessage());
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            $event->setFailed($exception->getMessage());
        }
    }

    /**
     * @return TriggerdialogService
     */
    protected function getTriggerDialogService(): TriggerdialogService
    {
        return TriggerdialogService::makeInstance(
            [],
            $this->coreParametersHelper->get('triggerdialog_masId'),
            $this->coreParametersHelper->get('triggerdialog_masClientId'),
            $this->coreParametersHelper->get('triggerdialog_authenticationSecret')
        );
    }
}
