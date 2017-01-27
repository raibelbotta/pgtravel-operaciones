<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityManager;

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
                ->add('description', TextareaType::class, array(
                    'required' => false
                ))
                ->add('pax')
                ->add('nights')
                ->add('supplierUnitPrice', null, array(
                    'label' => 'Unit price'
                ))
                ->add('supplierPrice')
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

    private function getSupplierQueryBuilder()
    {
        $queryBuilder = $this->manager->getRepository('AppBundle:Supplier')
                ->createQueryBuilder('s')
                ->orderBy('s.name')
                ;

        return $queryBuilder;
    }
}
