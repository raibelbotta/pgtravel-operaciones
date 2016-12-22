<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Description of PayReservationFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PayReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('payNotes', null, array(
                    'label' => 'Notes',
                    'required' => false
                ))
                ->add('payAttachments', CollectionType::class, array(
                    'label' => 'Attachments',
                    'entry_type' => ReservationPayAttachmentType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required' => false
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Reservation');
    }
}
