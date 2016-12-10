<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of ValidPlan
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class ValidPlan extends Constraint
{
    public $message = 'Invalid value. Valid values are [%plans%]';

    public function validatedBy()
    {
        return 'validator_plans';
    }
}
