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
    /**
     * @var array
     */
    private $types;

    public function __construct(array $types)
    {
        $this->types = array();
        foreach ($types as $type) {
            $this->types[$type['display']] = $type['name'];
        }
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->types,
            'choices_as_values' => true
        ));
    }
}
