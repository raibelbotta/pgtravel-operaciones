<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\ContractCarRentalSeassonDayRange;

/**
 * ContractCarRentalSeassonDayRangeType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractCarRentalSeassonDayRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('beginDay', null, array(
                    'label' => 'First day number'
                ))
                ->add('endDay', null, array(
                    'label' => 'Last day number'
                ))
                ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractCarRentalSeassonDayRange::class);
    }
}
