<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\ContractCarRentalSeassonDate;

/**
 * ContractCarRentalSeassonDateType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractCarRentalSeassonDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('startAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('endAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractCarRentalSeassonDate::class);
    }
}
