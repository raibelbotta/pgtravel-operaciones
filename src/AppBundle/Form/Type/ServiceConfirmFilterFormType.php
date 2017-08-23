<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

/**
 * Description of ServiceConfirmFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ServiceConfirmFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('isNotified', Filters\ChoiceFilterType::class, array(
            'choices' => array(
                'Confirmed' => 'yes',
                'Pending' => 'no'
            ),
            'choices_as_values' => true,
            'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value'])) {
                    return null;
                }

                $expression = $filterQuery->getExpr()->eq($field, $filterQuery->getExpr()->literal('yes' === $values['value']));

                return $filterQuery->createCondition($expression);
            },
            'label' => 'State'
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
