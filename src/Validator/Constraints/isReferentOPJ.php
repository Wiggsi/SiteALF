<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isReferentOPJ extends Constraint
{
    public $message = "Le gendarme choisi comme OPJ référent doit être OPJ.";

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}