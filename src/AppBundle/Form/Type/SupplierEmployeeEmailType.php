<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of SupplierEmployeeEmailType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class SupplierEmployeeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('email', null, array('required' => true))
                ->add('position', EmailPositionType::class)
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'AppBundle\Entity\SupplierEmployeeEmail');
    }
}
