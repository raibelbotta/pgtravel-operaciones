<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Description of PayReservationServiceFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PayReservationServiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('payNotes', null, array(
                    'required' => false,
                    'label' => 'Notes'
                ))
                ->add('payAttachments', CollectionType::class, array(
                    'entry_type' => ReservationServicePayAttachmentType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => false,
                    'label' => 'Attachments'
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ReservationService');
    }
}
