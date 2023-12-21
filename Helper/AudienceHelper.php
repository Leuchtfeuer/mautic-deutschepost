<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Helper;

class AudienceHelper
{
    private const AUDIENCE = 'https://api-eu.dhl.com/post/advertising/print-mailing';

    private const TEST_AUDIENCE = 'https://api-uat-vzen.dhl.com/post/advertising/print-mailing';

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
