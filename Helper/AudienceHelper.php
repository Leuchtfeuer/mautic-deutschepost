<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Helper;

class AudienceHelper
{
    private const AUDIENCE = 'https://dm.deutschepost.de';

    private const TEST_AUDIENCE = 'https://dm-uat.deutschepost.de';

    public static function getAudience(): string
    {
        return MAUTIC_ENV === 'prod' ? self::AUDIENCE : self::TEST_AUDIENCE;
    }
}
