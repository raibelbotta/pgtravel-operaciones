<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidServiceType extends Constraint
{
    public $message = '%service% is not valid service type';
}