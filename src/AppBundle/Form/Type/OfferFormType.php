<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityManager;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * OfferFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class OfferFormType extends AbstractType
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
        $builder
                ->add('clientType', ChoiceType::class, array(
                    'choices' => array(
                        'Registered' => 'registered',
                        'Direct'    => 'direct'
                    ),
                    'choices_as_values' => true,
                    'expanded' => true
                ))
                ->add('client', null, array(
                    'required' => false
                ))
                ->add('directClientFullName', null, array(
                    'label' => 'Full name',
                    'required' => false
                ))
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
                ))
                ->add('notificationLine', NotificationLineType::class, array(
                    'required' => false
                ))
                ->add('name')
                ->add('travelerNames', null, array(
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
                    'label'=> 'Representant',
                    'required' => false
                ))
                ->add('percentApplied', PercentAppliedType::class, array(
                    'required' => true
                ))
                ->add('totalExpenses', null, array(
                    'mapped' => false
                ))
                ->add('totalCharges', null, array(
                    'mapped' => false
                ))
                ;

        $manager = $this->manager;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($manager) {
            $data = $event->getData();
            $form = $event->getForm();

            $qb = $manager->getRepository('AppBundle:ClientContact')->createQueryBuilder('cc')
                    ->join('cc.client', 'c')
                    ->orderBy('cc.fullName');
            $andX = $qb->expr()->andX();

            if (null === $data->getId() || null === $data->getClient()) {
                $andX->add($qb->expr()->isNull('cc.id'));
            } else {
                $andX->add($qb->expr()->eq('c.id', $qb->expr()->literal($data->getClient()->getId())));
            }

            $form->add('notificationContact', null, array(
                'query_builder' => $qb->where($andX)
            ));

            if (null === $data->getId()) {
                $form->add('jumpToOperation', CheckboxType::class, array(
                    'label' => 'Put this offer in operation after save',
                    'mapped' => false,
                    'required' => false
                ));
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($manager) {
            $data = $event->getData();
            $form = $event->getForm();

            if ('direct' === $data['clientType']) {
                $form->add('notificationContact', ChoiceType::class, array(
                    'required' => false
                ));
                $data['client'] = '';

                $form
                        ->remove('directClientFullName')
                        ->add('directClientFullName', null, array(
                            'required' => true
                        ));
                $event->setData($data);
            } else {
                $qb = $manager->getRepository('AppBundle:ClientContact')->createQueryBuilder('cc')
                        ->join('cc.client', 'c')
                        ->orderBy('cc.fullName');
                $andX = $qb->expr()->andX($qb->expr()->eq('c.id', $qb->expr()->literal($data['client'])));

                $form
                        ->add('notificationContact', null, array(
                            'query_builder' => $qb->where($andX)
                        ))
                        ->remove('client')
                        ->add('client', null, array(
                            'required' => true
                        ))
                        ;
                
                $data['directClientFullName'] = '';
                $data['directClientEmail'] = '';
                $data['directClientPostalAddress'] = '';
                $data['directClientMobilePhone'] = '';

                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Reservation');
    }
}
