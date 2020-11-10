<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Validator;

use Symfony\Component\Validator\Constraint;

class AllowedCharacters extends Constraint
{
    const DISALLOWED_CHARACTERS = '/[\<\>\?\"\:\|\/\\\*]/';

    const ERROR_CODE = 1571659503;
}
