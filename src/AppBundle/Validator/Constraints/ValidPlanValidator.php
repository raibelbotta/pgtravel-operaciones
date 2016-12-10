<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Description of ValidPlanValidator
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ValidPlanValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $plans;

    public function __construct(array $plans)
    {
        $this->plans = $plans;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, $this->plans)) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('%plans%', $this->plans)
                    ->addViolation()
                    ;
        }
    }
}
