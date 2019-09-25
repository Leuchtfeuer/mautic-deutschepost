<?php
namespace MauticPlugin\MauticTriggerdialogBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;

class ConfigSubscriber extends CommonSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    /**
     * @param ConfigBuilderEvent $event
     */
    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm([
            'bundle' => 'MauticTriggerdialogBundle',
            'formAlias' => 'triggerdialogconfig',
            'formTheme' => 'MauticTriggerdialogBundle:FormTheme\Config',
            'parameters' => $event->getParametersFromConfig('MauticTriggerdialogBundle'),
        ]);
    }
}
