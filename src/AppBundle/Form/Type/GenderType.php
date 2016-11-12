<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of GenderType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class GenderType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Male'      => 'M',
                'Female'    => 'F'
            ),
            'choices_as_values' => true
        ));
    }
}
