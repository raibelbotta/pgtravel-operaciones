<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

/**
 * Description of SupplierEmployeeType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class SupplierEmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullName')
                ->add('gender', GenderType::class, array(
                    'required' => false
                ))
                ->add('fixedPhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('mobilePhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ->add('jobPosition')
                ->add('emails', CollectionType::class, array(
                    'entry_type' => SupplierEmployeeEmailType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\SupplierEmployee');
    }
}
