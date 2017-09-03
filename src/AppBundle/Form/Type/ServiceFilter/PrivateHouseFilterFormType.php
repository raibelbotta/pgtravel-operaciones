<?php

namespace AppBundle\Form\Type\ServiceFilter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Province;

/**
 * Description of PrivateHouseFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PrivateHouseFilterFormType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var array
     */
    private $plans;

    public function __construct(EntityManager $manager, array $plans)
    {
        $this->manager = $manager;
        $this->plans = $plans;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('dates', Filters\DateRangeFilterType::class, array(
                    'left_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'label' => 'From',
                        'widget' => 'single_text'
                    ),
                    'right_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'label' => 'To',
                        'widget' => 'single_text'
                    ),
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value']['left_date'][0]) && empty($values['value']['right_date'][0])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->andX();
                        $params = array();

                        if (!empty($values['value']['left_date'][0])) {
                            $expression->add($filterQuery->getExpr()->orX(
                                    $filterQuery->getExpr()->isNull('c.startAt'),
                                    $filterQuery->getExpr()->lte('c.startAt', ':p_dates_left')
                                    ));
                            $params['p_dates_left'] = $values['value']['left_date'][0]->format('Y-m-d 00:00:00');
                        }

                        if (!empty($values['value']['right_date'][0])) {
                            $expression->add($filterQuery->getExpr()->orX(
                                    $filterQuery->getExpr()->isNull('c.endAt'),
                                    $filterQuery->getExpr()->gte('c.endAt', ':p_dates_right')
                                    ));
                            $params['p_dates_right'] = $values['value']['right_date'][0]->format('Y-m-d 23:59:59');
                        }

                        return $filterQuery->createCondition($expression, $params);
                    }
                ))
                ->add('nights', IntegerType::class, array(
                    'required' => false,
                    'apply_filter' => false
                ))
                ->add('address', Filters\TextFilterType::class, array(
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->andX(
                                $filterQuery->getExpr()->isNotNull('s.postalAddress'),
                                $filterQuery->getExpr()->like('s.postalAddress', ':p_address')
                                );

                        return $filterQuery->createCondition($expression, array(
                            'p_address' => sprintf('%%%s%%', $values['value'])
                        ));
                    }
                ))
                ->add('province', Filters\EntityFilterType::class, array(
                    'class' => Province::class,
                    'query_builder' => $this->manager->getRepository('AppBundle:Province')
                        ->createQueryBuilder('p')
                        ->orderBy('p.name'),
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $filterQuery->getQueryBuilder()->join('s.province', 'pr');
                        $expression = $filterQuery->getExpr()->eq('pr.id', ':p_province');

                        return $filterQuery->createCondition($expression, array(
                            'p_province' => $values['value']->getId()
                        ));
                    }
                ))
                ->add('plan', Filters\ChoiceFilterType::class, array(
                    'choices' => array_combine($this->plans, $this->plans),
                    'choices_as_values' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->eq('f.mealPlan', ':p_plan');

                        return $filterQuery->createCondition($expression, array('p_plan' => $values['value']));
                    }
                ))
                ->add('quantity', ChoiceType::class, array(
                    'choices' => array_combine(range(1, 10), range(1, 10)),
                    'label' => 'Number of rooms',
                    'required' => true,
                    'apply_filter' => false
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
}
