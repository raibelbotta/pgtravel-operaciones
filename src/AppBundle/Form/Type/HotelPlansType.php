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
    /**
     * @var array
     */
    private $plans;

    public function __construct(array $plans)
    {
        $this->plans = $plans;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array_combine($this->plans, $this->plans),
            'multiple' => true
        ));
    }
}
