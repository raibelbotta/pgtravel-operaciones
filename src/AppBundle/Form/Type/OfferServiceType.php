<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\ReservationService;

/**
 * Description of OfferServiceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class OfferServiceType extends AbstractType
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
                ->add('name')
                ->add('model', OfferServiceModelType::class, array(
                    'label' => 'Type'
                ))
                ->add('description')
                ->add('clientName', null, array(
                    'label' => 'Person in charge'
                ))
                ->add('facilityName')
                ->add('pax')
                ->add('nights')
                ->add('origin', PlaceType::class, array(
                    'required' => false
                ))
                ->add('destination', PlaceType::class, array(
                    'required' => false
                ))
                ->add('rentCar', RentCarType::class, array(
                    'required' => false
                ))
                ->add('cost')
                ->add('totalPrice')
                ->add('supplier', null, array(
                    'required' => false,
                    'query_builder' => $this->getSupplierQueryBuilder()
                ))
                ->add('startAt', null, array(
                    'format'    => 'dd/MM/yyyy HH:mm',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('endAt', null, array(
                    'format'    => 'dd/MM/yyyy HH:mm',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('internalNotes')
                ->add('supplierNotes')
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReservationService::class);
    }

    private function getSupplierQueryBuilder()
    {
        $queryBuilder = $this->manager->getRepository('AppBundle:Supplier')
                ->createQueryBuilder('s')
                ->orderBy('s.name')
                ;

        return $queryBuilder;
    }
}
