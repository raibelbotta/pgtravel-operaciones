<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Constants\ContractModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidServiceTypeValidator extends ConstraintValidator
{
    /**
     * @param string $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, array_keys(ContractModel::MODELS))) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%service%', $value)
                ->addViolation();
        }
    }
}
