<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OfferHasOneServiceAtLeastValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (0 === $value->getServices()->count()) {
            $this->context->buildViolation($constraint->message)
                    ->atPath('services')
                    ->addViolation()
                    ;
        }
    }
}