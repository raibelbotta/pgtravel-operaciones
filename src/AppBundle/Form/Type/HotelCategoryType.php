<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of HotelCategoryType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class HotelCategoryType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Two stars'     => 'TWO_STAR',
                'Three stars'   => 'THREE_STAR',
                'Four stars'   => 'FOUR_STAR',
                'Five stars'   => 'FIVE_STAR'
            ),
            'choices_as_values' => true
        ));
    }
}
