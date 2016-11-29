<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
/**
 * Description of ContractModelType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractModelType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'HOTEL' => 'hotel',
                'TAXI' => 'taxi'
            ),
            'choices_as_values' => true
        ));
    }
}
