<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ContractCarRentalCategory;

/**
 * ContractCarRentalCategoryType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractCarRentalCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractCarRentalCategory::class);
    }
}
