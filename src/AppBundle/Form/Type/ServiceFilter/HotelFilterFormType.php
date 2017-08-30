<?php

namespace AppBundle\Form\Type\ServiceFilter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * HotelFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class HotelFilterFormType extends AbstractType
{
    /**
     * @var array
     */
    private $cupos;

    /**
     * @var array
     */
    private $plans;

    public function __construct(array $cupos, array $plans)
    {
        $this->cupos = $cupos;
        $this->plans = $plans;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('dates', Filters\DateRangeFilterType::class, array(
                    'left_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text'
                    ),
                    'right_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text'
                    ),
                    'apply_filter' => array($this, 'applyDatesFilter')
                ))
                ->add('nights', IntegerType::class, array(
                    'required' => false,
                    'apply_filter' => false
                ))
                ->add('quantity', ChoiceType::class, array(
                    'choices' => array_combine(range(1, 10), range(1, 10)),
                    'choices_as_values' => true,
                    'label' => 'Number of rooms',
                    'apply_filter' => false
                ))
                ->add('cupo', Filters\ChoiceFilterType::class, array(
                    'choices' => array_combine($this->cupos, $this->cupos),
                    'choices_as_values' => true,
                    'label' => 'PAX'
                ))
                ->add('plan', Filters\ChoiceFilterType::class, array(
                    'choices' => array_combine($this->plans, $this->plans),
                    'choices_as_values' => true
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'validation_groups' => array('filtering')
        ));
    }

    public function applyDatesFilter(QueryInterface $filterQuery, $field, $values)
    {
        if (empty($values['value']['left_date'][0]) && empty($values['value']['right_date'][0])) {
            return null;
        }

        $expression = $filterQuery->getExpr()->andX();
        $params = array();

        if (!empty($values['value']['left_date'][0])) {
            $expression->add($filterQuery->getExpr()->andX(
                    $filterQuery->getExpr()->lte('c.startAt', ':p_hf_from_left'),
                    $filterQuery->getExpr()->lte('s.fromDate', ':p_hf_from_left'),
                    $filterQuery->getExpr()->gte('s.toDate', ':p_hf_from_left')
            ));
            $params[':p_hf_from_left'] = $values['value']['left_date'][0]->format('Y-m-d 00:00:00');
        }

        if (!empty($values['value']['right_date'][0])) {
            $expression->add($filterQuery->getExpr()->andX(
                    $filterQuery->getExpr()->gte('c.endAt', ':p_hf_from_right'),
                    $filterQuery->getExpr()->gte('s.toDate', ':p_hf_from_right'),
                    $filterQuery->getExpr()->lte('s.fromDate', ':p_hf_from_right')
            ));
            $params[':p_hf_from_right'] = $values['value']['right_date'][0]->format('Y-m-d 23:59:59');
        }

        return $filterQuery->createCondition($expression, $params);
    }
}
