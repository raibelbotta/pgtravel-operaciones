<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

/**
 * Description of ClientFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ClientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullName')
                ->add('fixedPhone', PhoneNumberType::class, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ->add('contacts', CollectionType::class, array(
                    'entry_type' => ClientContactType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Client');
    }
}
