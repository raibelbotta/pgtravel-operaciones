<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

/**
 * Description of UserFormType
 *
 * @author raibel
 */
class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('firstName')
                ->add('lastName')
                ->add('email')
                ->add('gender', GenderType::class)
                ->add('roles', ChoiceType::class, array(
                    'multiple' => true,
                    'choices' => array(
                        'System super admin'    => 'ROLE_SUPER_ADMIN',
                        'Primary data'          => 'ROLE_ADMIN',
                        'Offers'                => 'ROLE_OFFERS',
                        'Reservation'           => 'ROLE_RESERVATIONS',
                        'Accounts'              => 'ROLE_ACCOUNTS',
                        'Reports'               => 'ROLE_REPORTS'
                    ),
                    'choices_as_values' => true,
                    'label' => 'Access control'
                ))
                ->add('mobilePhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => array(
                    'label' => 'New password'
                ),
                'second_options' => array(
                    'label' => 'Repeat password'
                ),
                'required' => null === $data->getId()
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => array('Profile')
        ));
    }
}
