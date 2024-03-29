<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class PrintmailingIntegration extends AbstractIntegration
{
    public const PLUGIN_NAME = 'Printmailing';

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
