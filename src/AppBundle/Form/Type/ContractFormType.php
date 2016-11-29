<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

/**
 * Description of ContractFormType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('model', ContractModelType::class)
                ->add('supplier')
                ->add('name')
                ->add('description', TextareaType::class, array(
                    'required' => false
                ))
                ->add('notes', TextareaType::class, array(
                    'required' => false
                ))
                ->add('signedAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('startAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('endAt', null, array(
                    'format'    => 'dd/MM/yyyy',
                    'html5'     => false,
                    'widget'    => 'single_text'
                ))
                ->add('extraConditions', CKEditorType::class, array(
                    'required' => false
                ))
                ->add('topServices', CollectionType::class, array(
                    'entry_type' => ContractTopServiceType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ->add('attachments', CollectionType::class, array(
                    'entry_type' => ContractAttachmentType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ->add('facilities', CollectionType::class, array(
                    'entry_type' => ContractFacilityType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Contract');
    }
}
