<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\ContractPrivateHouseFacility;

/**
 * Description of ContractPrivateHouseFacilityType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractPrivateHouseFacilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('mealPlan', MealPlanType::class)
                ->add('notes')
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractPrivateHouseFacility::class);
    }
}
