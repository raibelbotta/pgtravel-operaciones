<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of ValidCupo
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class ValidCupo extends Constraint
{
    public $message = 'Invalid value. Valid values are [%cupos%]';

    public function validatedBy()
    {
        return 'validator_cupos';
    }
}
