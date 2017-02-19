<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of EmailPositionType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class EmailPositionType extends AbstractType
{
    public function __construct(array $positions)
    {
        $this->positions = $positions;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array_combine($this->positions, $this->positions),
            'values_as_choices' => true
        ));
    }
}
