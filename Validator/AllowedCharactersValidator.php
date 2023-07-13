<?php

namespace MauticPlugin\LeuchtfeuerPrintmailingBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AllowedCharactersValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (0 === preg_match(AllowedCharacters::DISALLOWED_CHARACTERS, $value)) {
            return;
        }

        $this->context
            ->buildViolation('Name or Print Node Description may not contain following characters: < > ? " : | \\ / *')
            ->setInvalidValue($value)
            ->setCode(Variable::ERROR_CODE)
            ->addViolation();
    }
}
