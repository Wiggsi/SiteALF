<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RegistrationValidator extends ConstraintValidator {
    public function validate($value, Constraint $constraint)
    {
        if ($value->getUsername() != $value->getFirstName().' '.$value->getName()) {
            $this->context->buildViolation($constraint->message)->atPath('username')
                ->addViolation();
        }
    }
}
