<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Validator;

use Symfony\Component\Validator\Constraint;

class Variable extends Constraint
{
    public const REQUIRED_FIELD = 'zip';

    public const ERROR_CODE = 1569235168;
}
