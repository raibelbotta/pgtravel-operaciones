<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ContractPrivateHouseSeason;

/**
 * Description of ContractPrivateHouseSeassonType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ContractPrivateHouseSeassonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name')
                ->add('startAt', null, array(
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text'
                ))
                ->add('endAt', null, array(
                    'format' => 'dd/MM/yyyy',
                    'html5' => false,
                    'widget' => 'single_text'
                ))
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ContractPrivateHouseSeason::class);
    }
}
