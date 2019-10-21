<?php

use MauticPlugin\MauticTriggerdialogBundle\EventListener\CampaignSubscriber;
use MauticPlugin\MauticTriggerdialogBundle\EventListener\ConfigSubscriber;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ActionType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\ConfigType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\TriggerCampaignType;
use MauticPlugin\MauticTriggerdialogBundle\Form\Type\VariableType;
use MauticPlugin\MauticTriggerdialogBundle\Model\TriggerCampaignModel;

return [
    'name' => 'Dt. Post',
    'description' => 'Send postcards or letters via Deutsche Post TRIGGERDIALOG',
    'version' => '1.0.1',
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
    ],

    'parameters' => [
        'triggerdialog_masClientId' => null,
        'triggerdialog_masSecret' => null,
        'triggerdialog_email' => null,
        'triggerdialog_username' => null,
        'triggerdialog_firstName' => null,
        'triggerdialog_lastName' => null,
        'triggerdialog_masId' => '55',
        'triggerdialog_rest_user' => null,
        'triggerdialog_rest_password' => null,
    ],
];
