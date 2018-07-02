<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ReservationPaxRevenueLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ReservationPaxRevenueLineFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservationPaxRevenueLineFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', ChoiceType::class, array(
                'choices' => array(
                    'SGL' => 'SGL',
                    'DBL' => 'DBL',
                    'TRP' => 'TRP',
                    'QUAD' => 'QUAD'
                ),
                'choices_as_values' => true
            ))
            ->add('nights', null, array(
                'data' => 1
            ))
            ->add('pax', null, array(
                'data' => 1
            ))
            ->add('price')
            ->add('total');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReservationPaxRevenueLine::class);
    }
}
