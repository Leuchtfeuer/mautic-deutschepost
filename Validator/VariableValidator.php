<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VariableValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($value as $variable) {
            if ($variable['variable'] === Variable::REQUIRED_FIELD) {
                return;
            }
        }

        $this->context
            ->buildViolation('A variable of the data type ZIP is mandatory')
            ->setInvalidValue($value)
            ->setCode(Variable::ERROR_CODE)
            ->addViolation();
    }
}
