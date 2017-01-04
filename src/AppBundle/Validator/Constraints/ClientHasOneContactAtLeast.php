<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of ClientHasOneContactAtLeast
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class ClientHasOneContactAtLeast extends Constraint
{
    public $message = 'Client has no contact person';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
