<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Validator;

use Symfony\Component\Validator\Constraint;

class Variable extends Constraint
{
    public const REQUIRED_FIELD = 'zip';

    public const ERROR_CODE = 1569235168;
}
