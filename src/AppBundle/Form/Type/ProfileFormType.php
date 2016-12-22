<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of ProfileFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
                ->remove('username')
                ->remove('plainPassword')
                ->add('firstName')
                ->add('lastName')
                ->add('gender', GenderType::class)
                ->add('mobilePhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ;
    }
}