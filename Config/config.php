<?php

use MauticPlugin\MauticTriggerdialogBundle\EventListener\CampaignSubscriber;
use MauticPlugin\MauticTriggerdialogBundle\EventListener\ConfigSubscriber;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ActionType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ConfigType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\TriggerCampaignType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\VariableType;
use MauticPlugin\MauticTriggerdialogBundle\Integration\TriggerdialogIntegration;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;
use MauticPlugin\MauticTriggerdialogBundle\Utility\SsoUtility;

return [
    'name' => 'Dt. Post',
    'description' => 'Send postcards or letters via Deutsche Post TRIGGERDIALOG',
    'version' => '1.1.0',
    'author' => 'Florian Wessels',

    'menu' => [
        'main' => [
            'plugin.triggerdialog.menu.index' => [
                'route' => 'mautic_triggerdialog_index',
                'parent' => 'mautic.core.channels',
                'access' => [
                    'triggerdialog:campaigns:view',
                ],
                'priority' => 50,
            ],
        ],
    ],

    'routes' => [
        'main' => [
            'mautic_triggerdialog_index' => [
                'path' => '/triggertemplates/{page}',
                'controller' => 'MauticTriggerdialogBundle:TriggerCampaign:index',
            ],
            'mautic_triggerdialog_action' => [
                'path' => '/triggertemplates/{objectAction}/{objectId}',
                'controller' => 'MauticTriggerdialogBundle:TriggerCampaign:execute',
            ],
        ],
    ],

    'services' => [
        'integrations' => [
            'mautic.integration.triggerdialog' => [
                'class'     => TriggerdialogIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
        'events' => [
            'mautic.triggerdialog.config.subscriber' => [
                'class' => ConfigSubscriber::class,
            ],
            'mautic.triggerdialog.campaign.subscriber' => [
                'class' => CampaignSubscriber::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.triggerdialog.model.campaign',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.triggerdialogconfig' => [
                'class' => ConfigType::class,
                'alias' => 'triggerdialogconfig',
            ],
            'mautic.form.type.trigger_campaign' => [
                'class' => TriggerCampaignType::class,
                'alias' => 'trigger_campaign',
                'arguments' => [
                    'mautic.factory',
                    'mautic.lead.model.list',
                ],
            ],
            'mautic.form.type.trigger_variable' => [
                'class' => VariableType::class,
                'alias' => 'trigger_variable',
            ],
            'mautic.form.type.trigger_action' => [
                'class' => ActionType::class,
                'alias' => 'trigger_action',
                'arguments' => [
                    'mautic.triggerdialog.model.campaign',
                ],
            ],
        ],
        'models' => [
            'mautic.triggerdialog.model.campaign' => [
                'class' => TriggerCampaignModel::class,
            ],
        ],
        'utilities' => [
            'mautic.triggerdialog.utility.sso' => array(
                'class' => SsoUtility::class,
                'alias' => 'sso_utility',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.user'
                ]
            )
        ]
    ],

    'parameters' => [
        'triggerdialog_masClientId' => \MauticPlugin\MauticTriggerdialogBundle\Generator\ClientIdGenerator::generateClientId(),
        'triggerdialog_masSecret' => null,
        'triggerdialog_email' => null,
        'triggerdialog_username' => null,
        'triggerdialog_firstName' => null,
        'triggerdialog_lastName' => null,
        'triggerdialog_masId' => null,
        'triggerdialog_rest_user' => null,
        'triggerdialog_rest_password' => null,
    ],
];
