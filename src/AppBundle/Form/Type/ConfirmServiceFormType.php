<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityManager;

/**
 * Description of ConfirmServiceFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ConfirmServiceFormType extends AbstractType
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
        $qb = $this->manager->getRepository('AppBundle:Supplier')
                ->createQueryBuilder('s')
                ->orderBy('s.name')
                ;

        $builder
                ->add('supplier', null, array(
                    'query_builder' => $qb,
                    'required' => true
                ))
                ->add('supplierReference', TextareaType::class, array(
                    'label' => 'Reference',
                    'required' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ReservationService');
    }
}
