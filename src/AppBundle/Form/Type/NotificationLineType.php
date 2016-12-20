<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Description of NotificationLineType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class NotificationLineType extends AbstractType
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array_combine(array_map(function($e) {
                return ucfirst(\str_replace('-', ' ', $e));
            }, $this->values), $this->values),
            'choices_as_values' => true
        ));
    }
}
