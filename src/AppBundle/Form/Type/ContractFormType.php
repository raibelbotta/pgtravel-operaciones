<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
                ->add('supplier')
                ->add('name')
                ->add('description', TextareaType::class, array(
                    'required' => false
                ))
                ->add('notes', TextareaType::class, array(
                    'required' => false
                ))
                ->add('signedAt', null, array(
                    'html5' => false,
                    'widget' => 'single_text'
                ))
                ->add('startAt', null, array(
                    'html5' => false,
                    'widget' => 'single_text'
                ))
                ->add('endAt', null, array(
                    'html5' => false,
                    'widget' => 'single_text'
                ))
                ->add('topServices', CollectionType::class, array(
                    'type' => ContractTopServiceType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ))
                ->add('attachments', CollectionType::class, array(
                    'type' => ContractAttachmentType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\Contract');
    }
}
