<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Helper;

class AudienceHelper
{
    private const AUDIENCE = 'https://print-mailing-api.deutschepost.de';

    private const TEST_AUDIENCE = 'https://uat.print-mailing-api-test.deutschepost.de';

    private const FRONTEND = 'https://print-mailing.deutschepost.de';

    private const TEST_FRONTEND = 'https://uat.print-mailing-test.deutschepost.de';

    public static function getAudience(): string
    {
        return MAUTIC_ENV === 'prod' ? self::AUDIENCE : self::TEST_AUDIENCE;
    }

    public static function getFrontend(): string
    {
        return MAUTIC_ENV === 'prod' ? self::FRONTEND : self::TEST_FRONTEND;
    }
}
