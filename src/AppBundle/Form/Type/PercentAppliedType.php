<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Description of PercentAppliedType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PercentAppliedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('percent', ChoiceType::class, array(
                    'choices' => array(
                        '10%' => 10,
                        '15%' => 15,
                        '20%' => 20,
                        '30%' => 30,
                        'Add plus' => 'plus'
                    ),
                    'choices_as_values' => true,
                    'required' => $options['required']
                ))
                ->add('plus', TextType::class, array(
                    'required' => $options['required']
                ))
                ->addModelTransformer(new CallbackTransformer(
                    function($value) {
                        if (!$value) {
                            return array(
                                'percent'   => 30,
                                'plus'      => 0
                            );
                        }
                        return array(
                            'percent'   => false !== strpos($value, '%') ? $value : 'plus',
                            'plus'      => false === strpos($value, '%') ? $value : 0
                        );
                    },
                    function($value) {
                        return $value['percent'] !== 'plus' ? $value['percent'] : $value['plus'];
                    }
                ))
                ;
    }
}