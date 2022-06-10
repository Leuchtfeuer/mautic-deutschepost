<?php

namespace MauticPlugin\MauticTriggerdialogBundle\Helper;

class AudienceHelper
{
    private const AUDIENCE = 'https://print-mailing-api.deutschepost.de';

    private const TEST_AUDIENCE = 'https://uat.print-mailing-api-test.deutschepost.de';

    public static function getAudience(): string
    {
        return MAUTIC_ENV === 'prod' ? self::AUDIENCE : self::TEST_AUDIENCE;
    }
}
