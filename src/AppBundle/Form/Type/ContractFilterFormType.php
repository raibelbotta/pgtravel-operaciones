<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * Description of ContractFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractFilterFormType extends AbstractType
{
    /**
     * @var array
     */
    private $models;

    public function __construct(array $models)
    {
        $this->models = $models;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('model', Filters\ChoiceFilterType::class, array(
            'choices' => array_combine(
                    array_map(function($options) {
                        return $options['display'];
                    }, $this->models),
                    array_keys($this->models)
                    ),
            'choices_as_values' => true,
            'label' => 'Type'
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }
}
