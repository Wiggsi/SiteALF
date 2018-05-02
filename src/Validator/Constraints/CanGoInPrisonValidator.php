<?php

namespace App\Validator\Constraints;

use App\Entity\Criminel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanGoInPrisonValidator extends ConstraintValidator {
    public function validate($value, Constraint $constraint)
    {
        $message = NULL;
        if (!$value->getCriminel()->isFree() and $value->getCriminel()->getFichePrison() != $value) {
            $type = $value->getCriminel()->getFichePrison()->getType();
            if ($type == "Bracelet électronique") $message = "Cet individu est sous surveillance électronique.";
            else if ($type == "Condamné") $message = "Cet individu encourt déjà une peine de prison.";
            else if ($type == "GAV") $message = "Cet individu est en garde à vue.";
        }
        if ($message != NULL) {
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
