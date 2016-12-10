<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Description of CupoConstraintValidator
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ValidCupoValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $cupos;

    public function __construct(array $cupos)
    {
        $this->cupos = $cupos;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, $this->cupos)) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('%cupos%', $this->cupos)
                    ->addViolation()
                    ;
        }
    }
}
