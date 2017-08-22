<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Entity\Supplier;

/**
 * Description of SupplierFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class SupplierFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('fixedPhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('email')
                ->add('bankAccounts', null, array(
                    'label' => 'Bank data',
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ->add('province', ProvinceType::class, array(
                    'required' => false
                ))
                ->add('place', PlaceType::class, array(
                    'required' => false
                ))
                ->add('webAddress')
                ->add('employees', CollectionType::class, array(
                    'entry_type' => SupplierEmployeeType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Supplier::class);
    }
}
