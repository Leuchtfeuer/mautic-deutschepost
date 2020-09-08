<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Generator;

class ClientIdGenerator
{
    public static function generateClientId($bytes = 3)
    {
        return strtoupper(bin2hex(openssl_random_pseudo_bytes($bytes)));
    }
}
