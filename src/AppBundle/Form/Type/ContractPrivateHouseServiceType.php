<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ContractPrivateHouseServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractPrivateHouseServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('roomName')
                ->add('mealPlan', MealPlanType::class)
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
                ->add('price')
                ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ContractPrivateHouseService');
    }
}
