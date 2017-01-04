<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use AppBundle\Entity\Client;

/**
 * Description of ClientHasOneContactAtLeastValidator
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ClientHasOneContactAtLeastValidator extends ConstraintValidator
{
    /**
     * @param Client $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value->getContacts()->count() === 0) {
            $this->context->buildViolation($constraint->message)
                    ->addViolation()
                    ;
        }
    }
}
