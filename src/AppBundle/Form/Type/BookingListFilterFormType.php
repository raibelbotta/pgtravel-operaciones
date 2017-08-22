<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use AppBundle\Entity\Reservation;

/**
 * Description of BookingListFilterFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class BookingListFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('startAt', Filters\DateRangeFilterType::class, array(
                    'left_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text',
                        'label' => 'From'
                    ),
                    'right_date_options' => array(
                        'format' => 'dd/MM/yyyy',
                        'html5' => false,
                        'widget' => 'single_text',
                        'label' => 'To'
                    ),
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value']['left_date'][0]) && empty($values['value']['right_date'][0])) {
                            return null;
                        }

                        $params = array();
                        $expression = $filterQuery->getExpr()->andX();

                        if (!empty($values['value']['left_date'][0])) {
                            $params['p_startAt_left'] = $values['value']['left_date'][0]->format('Y-m-d 00:00:00');
                            $expression->add($filterQuery->getExpr()->gte(sprintf('(SELECT MIN(rs3.startAt) FROM AppBundle:ReservationService rs3 JOIN rs3.reservation rk3 WHERE rk3.id = %s.id)', $filterQuery->getRootAlias()), ':p_startAt_left'));
                        }

                        if (!empty($values['value']['right_date'][0])) {
                            $params['p_startAt_right'] = $values['value']['right_date'][0]->format('Y-m-d 23:59:59');
                            $expression->add($filterQuery->getExpr()->lte(sprintf('(SELECT MAX(rs4.endAt) FROM AppBundle:ReservationService rs4 JOIN rs4.reservation rk4 WHERE rk4.id = %s.id)', $filterQuery->getRootAlias()), ':p_startAt_right'));
                        }

                        return $filterQuery->createCondition($expression, $params);
                    }
                ))
                ->add('state', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'Offer' => Reservation::STATE_OFFER,
                        'Reservation' => Reservation::STATE_RESERVATION
                    ),
                    'choices_as_values' => true
                ))
                ->add('isCancelled', Filters\ChoiceFilterType::class, array(
                    'choices' => array(
                        'yes' => 'yes',
                        'no' => 'no'
                    ),
                    'choices_as_values' => true,
                    'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                        if (empty($values['value'])) {
                            return null;
                        }

                        $expression = $filterQuery->getExpr()->eq($field, $filterQuery->getExpr()->literal('yes' === $values['value']));

                        return $filterQuery->createCondition($expression);
                    }
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
