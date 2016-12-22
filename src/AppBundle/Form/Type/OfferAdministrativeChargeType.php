<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

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
                ->add('base')
                ->add('price')
                ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $label = in_array($data->getName(), array('Room', 'Board')) ? 'Number of nights' : 'Pax';

            $form->add('factor', null, array(
                'label' => $label
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ReservationAdministrativeCharge');
    }
}
