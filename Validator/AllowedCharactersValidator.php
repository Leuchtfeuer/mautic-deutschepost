<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AllowedCharactersValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {

        if (preg_match(AllowedCharacters::DISALLOWED_CHARACTERS, $value) === 0) {
            return;
        }

        $this->context
            ->buildViolation('Name or Print Node Description may not contain following characters: < > ? " : | \\ / *')
            ->setInvalidValue($value)
            ->setCode(Variable::ERROR_CODE)
            ->addViolation();
    }
}
