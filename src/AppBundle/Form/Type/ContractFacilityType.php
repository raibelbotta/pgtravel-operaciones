<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of ContractFacilityType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractFacilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('category', HotelCategoryType::class, array(
                    'required' => false
                ))
                ->add('postalAddress', TextareaType::class, array(
                    'required' => false
                ))
                ->add('activePlans', HotelPlansType::class)
                ->add('rooms', CollectionType::class, array(
                    'entry_type'    => ContractFacilityRoomType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false
                ))
                ->add('seasons', CollectionType::class, array(
                    'entry_type'    => ContractFacilitySeasonType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\ContractFacility');
    }
}
