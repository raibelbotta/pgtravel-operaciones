<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of OfferServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class OfferServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('description', TextareaType::class, array(
                    'required' => false
                ))
                ->add('pax')
                ->add('supplierPrice')
                ->add('supplier', null, array(
                    'required' => false
                ))
                ->add('startAt', null, array(
                    'format'    => 'dd/MM/yyyy HH:mm',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('endAt', null, array(
                    'format'    => 'dd/MM/yyyy HH:mm',
                    'html5'     => false,
                    'required'  => false,
                    'widget'    => 'single_text'
                ))
                ->add('internalNotes', TextareaType::class, array(
                    'required' => false
                ))
                ->add('supplierNotes', TextareaType::class, array(
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ReservationService');
    }
}
