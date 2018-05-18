<?php

namespace AppBundle\Constants;

/**
 * ContractModel
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractModel
{
    const MODELS = array(
        'car-rental' => array('name' => 'Car rental'),
        'guide' => array('name' => 'Guide'),
        'hotel' => array('name' => 'Hotel'),
        'optionals' => array('name' => 'Optionals'),
        'other' => array('name' => 'Other'),
        'private-house' => array('name' => 'Private house'),
        'restaurant' => array('name' => 'Restaurant'),
        'transport' => array('name' => 'Transportation')
    );

    public static function getModelNames()
    {
        $names = array();

        foreach (self::MODELS as $code => $model) {
            $names[$code] = $model['name'];
        }

        return $names;
    }
}
