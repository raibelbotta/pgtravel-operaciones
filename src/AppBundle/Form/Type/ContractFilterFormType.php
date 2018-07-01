<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Supplier;
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
        $builder
            ->add(
                'model',
                Filters\ChoiceFilterType::class,
                array(
                    'choices' => array_combine(
                        array_map(function($options) {
                            return $options['display'];
                        }, $this->models),
                        array_keys($this->models)
                    ),
                    'choices_as_values' => true,
                    'label' => 'Type'
                )
            )->add(
                'name',
                Filters\TextFilterType::class
            )->add(
                'supplier',
                Filters\TextFilterType::class,
                array(
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $paramName = sprintf('p_%s', str_replace('.', '_', $field));
                        $rootAlias = $filterQuery->getRootAlias();
                        $filterQuery->getQueryBuilder()->join(sprintf('%s.supplier', $rootAlias), 's');
                        $expression = $filterQuery->getExpr()->like('s.name', ':' . $paramName);

                        return $filterQuery->createCondition($expression, array(
                            $paramName => sprintf('%%%s%%', $values['value'])
                        ));
                    }
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }
}
