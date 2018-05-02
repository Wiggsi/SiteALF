<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class isReferentOPJValidator extends ConstraintValidator {
    public function validate($value, Constraint $constraint)
    {
        if (!$value->getOPJ()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}