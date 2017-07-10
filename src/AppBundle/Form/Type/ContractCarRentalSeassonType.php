<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\ContractCarRentalSeasson;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * ContractCarRentalSeassonType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractCarRentalSeassonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('dates', CollectionType::class, array(
                    'entry_type' => ContractCarRentalSeassonDateType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ->add('dayRanges', CollectionType::class, array(
                    'entry_type' => ContractCarRentalSeassonDayRangeType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractCarRentalSeasson::class);
    }
}
