<?php

use MauticPlugin\LeuchtfeuerPrintmailingBundle\EventListener\CampaignSubscriber;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\EventListener\ConfigSubscriber;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\ActionType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\ConfigType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\TriggerCampaignType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\VariableType;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Generator\ClientIdGenerator;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Integration\PrintmailingIntegration;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Model\TriggerCampaignModel;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Utility\SingleSignOnUtility;

return [
    'name'        => 'Print Mailing DPAG Integration by Leuchtfeuer',
    'description' => 'Send postcards or letters via Print Mailing',
    'version'     => '5.1.2',
    'author'      => 'Leuchtfeuer Digital Marketing GmbH',

    'menu' => [
        'main' => [
            'plugin.printmailing.menu.index' => [
                'route'  => 'mautic_printmailing_index',
                'parent' => 'mautic.core.channels',
                'access' => [
                    'printmailing:campaigns:view',
                ],
                'checks' => [
                    'integration' => [
                        'Printmailing' => [
                            'enabled' => true,
                        ],
                    ],
                ],
                'priority' => 50,
            ],
        ],
    ],

    'routes' => [
        'main' => [
            'mautic_printmailing_index' => [
                'path'       => '/triggertemplates/{page}',
                'controller' => 'LeuchtfeuerPrintmailingBundle:TriggerCampaign:index',
            ],
            'mautic_printmailing_action' => [
                'path'       => '/triggertemplates/{objectAction}/{objectId}',
                'controller' => 'LeuchtfeuerPrintmailingBundle:TriggerCampaign:execute',
            ],
        ],
    ],

    'services' => [
        'integrations' => [
            'mautic.integration.printmailing' => [
                'class'     => PrintmailingIntegration::class,
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
            'mautic.printmailing.config.subscriber' => [
                'class' => ConfigSubscriber::class,
            ],
            'mautic.printmailing.campaign.subscriber' => [
                'class'     => CampaignSubscriber::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.printmailing.model.campaign',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.printmailingconfig' => [
                'class' => ConfigType::class,
                'alias' => 'printmailingconfig',
            ],
            'mautic.form.type.trigger_campaign' => [
                'class'     => TriggerCampaignType::class,
                'alias'     => 'trigger_campaign',
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
                'class'     => ActionType::class,
                'alias'     => 'trigger_action',
                'arguments' => [
                    'mautic.printmailing.model.campaign',
                ],
            ],
        ],
        'models' => [
            'mautic.printmailing.model.campaign' => [
                'class' => TriggerCampaignModel::class,
            ],
        ],
        'utilities' => [
            'mautic.printmailing.utility.sso' => [
                'class'     => SingleSignOnUtility::class,
                'alias'     => 'sso_utility',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.user',
                ],
            ],
        ],
    ],

    'parameters' => [
        'printmailing_masClientId'    => ClientIdGenerator::generateClientId(),
        'printmailing_masSecret'      => null,
        'printmailing_masId'          => null,
        'printmailing_rest_user'      => null,
        'printmailing_rest_password'  => null,
        'printmailing_contract_email' => 'print-mailing@deutschepost.de',
    ],
];
