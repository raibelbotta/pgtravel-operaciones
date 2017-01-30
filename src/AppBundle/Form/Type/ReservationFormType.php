<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;

/**
 * Description of ReservationFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ReservationFormType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manager = $this->manager;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($manager) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null !== $data->getClient()) {
                $qb = $manager->getRepository('AppBundle:ClientContact')->createQueryBuilder('cc')
                    ->join('cc.client', 'c')
                    ->orderBy('cc.fullName');
                $andX = $qb->expr()->andX($qb->expr()->eq('c.id', $data->getClient()->getId()));

                $form->add('notificationContact', null, array(
                    'query_builder' => $qb->where($andX),
                    'required' => false
                ));
            } else {
                $form
                        ->add('directClientEmail', null, array(
                            'label' => 'Email',
                            'required' => false
                        ))
                        ->add('directClientPostalAddress', null, array(
                            'label' => 'Postal address',
                            'required' => false
                        ))
                        ->add('directClientMobilePhone', PhoneNumberType::class, array(
                            'label' => 'Mobile phone',
                            'required' => false
                        ));
            }
        });

        $builder
                ->add('notificationLine', NotificationLineType::class, array(
                    'label' => 'Request via',
                    'required' => false
                ))
                ->add('name')
                ->add('travelerNames', TextareaType::class, array(
                    'required' => false
                ))
                ->add('fliesData', null, array(
                    'label' => 'Arrival and departure flies',
                    'required' => false
                ))
                ->add('services', CollectionType::class, array(
                    'entry_type' => OfferServiceType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ->add('administrativeCharges', CollectionType::class, array(
                    'entry_type' => OfferAdministrativeChargeType::class,
                    'by_reference' => false
                ))
                ->add('offerSummaryFile', VichFileType::class, array(
                    'required' => false,
                    'label' => 'Travel itinerary document'
                ))
                ->add('clientCharge', null, array(
                    'required' => true
                ))
                ->add('operator', OperatorType::class, array(
                    'label' => 'Representant',
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Reservation');
    }
}
