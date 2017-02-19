<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of ValidEmailPosition
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class ValidEmailPosition extends Constraint
{
    public $message = 'Invalid value. Valid values are [%positions%]';

    public function validatedBy()
    {
        return 'validator_email_position';
    }
}
