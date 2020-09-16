<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class TriggerdialogIntegration extends AbstractIntegration
{

    public function getName()
    {
        return "Triggerdialog";
    }

    public function getAuthenticationType()
    {
        return 'oauth2';
    }
}