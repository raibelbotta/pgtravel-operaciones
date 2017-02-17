<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ContractCarRentalServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractCarRentalServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('price', null, array(
                    'required' => 'Price per day'
                ))
                ->add('name')
                ->add('carType')
                ->add('startAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text',
                    'required'  => false
                ))
                ->add('endAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text',
                    'required'  => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ContractCarRentalService');
    }
}
