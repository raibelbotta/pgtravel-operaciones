<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Description of ValidEmailPositionConstraintValidator
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ValidEmailPositionValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $positions;

    public function __construct(array $positions)
    {
        $this->positions = $positions;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, $this->positions)) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('%positions%', $this->positions)
                    ->addViolation()
                    ;
        }
    }
}
