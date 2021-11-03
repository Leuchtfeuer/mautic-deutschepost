<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

class TriggerdialogIntegration extends AbstractIntegration
{
    const PLUGIN_NAME = 'Triggerdialog';

    public function getName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function getDisplayName(): string
    {
        return 'Printmailing: Deutsche Post';
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }




}