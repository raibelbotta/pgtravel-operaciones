<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of HotelPlansType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class HotelPlansType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'CP' => 'CP',
                'TI' => 'TI'
            ),
            'multiple' => true
        ));
    }
}
