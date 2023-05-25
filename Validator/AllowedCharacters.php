<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Validator;

use Symfony\Component\Validator\Constraint;

class AllowedCharacters extends Constraint
{
    public const DISALLOWED_CHARACTERS = '/[\<\>\?\"\:\|\/\\\*]/';

    public const ERROR_CODE = 1571659503;
}
