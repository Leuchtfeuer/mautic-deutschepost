<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use MauticPlugin\LeuchtfeuerPrintmailingBundle\Form\Type\ConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        $event->addForm([
            'bundle'     => 'LeuchtfeuerPrintmailingBundle',
            'formType'   => ConfigType::class,
            'formAlias'  => 'triggerdialogconfig',
            'formTheme'  => 'LeuchtfeuerPrintmailingBundle:FormTheme\Config',
            'parameters' => $event->getParametersFromConfig('LeuchtfeuerPrintmailingBundle'),
        ]);
    }
}
