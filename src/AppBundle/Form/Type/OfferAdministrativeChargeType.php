<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AppBundle\Entity\ReservationAdministrativeCharge;

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
                ->add('multiplier')
                ->add('pax')
                ->add('price')
                ->add('total')
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReservationAdministrativeCharge::class);
    }
}
