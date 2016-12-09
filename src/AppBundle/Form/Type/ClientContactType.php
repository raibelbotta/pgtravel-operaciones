<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of ClientContactType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ClientContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullName')
                ->add('gender', GenderType::class, array(
                    'required' => false
                ))
                ->add('mobilePhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('fixedPhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('emailAddress', null, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ->add('notes', TextareaType::class, array(
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ClientContact');
    }
}
