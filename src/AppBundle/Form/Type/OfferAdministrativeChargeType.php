<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of OfferAdministrativeChargeType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class OfferAdministrativeChargeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('factor', null, array(
                    'label' => 'Pax'
                ))
                ->add('base')
                ->add('price')
                ->add('notes', TextareaType::class, array(
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ReservationAdministrativeCharge');
    }
}
