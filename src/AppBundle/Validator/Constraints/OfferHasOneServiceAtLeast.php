<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of OfferHasOneServiceAtLeast
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Annotation
 */
class OfferHasOneServiceAtLeast extends Constraint
{
    public $message = 'Offer has to have one service at least';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}