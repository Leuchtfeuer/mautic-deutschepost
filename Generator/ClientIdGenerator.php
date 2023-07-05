<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Generator;

class ClientIdGenerator
{
    public static function generateClientId(int $bytes = 3): string
    {
        return strtoupper(bin2hex(openssl_random_pseudo_bytes($bytes)));
    }
}
