<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use AppBundle\Entity\Place;

/**
 * PlaceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class PlaceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class'         => Place::class,
            'query_builder' => $this->manager->getRepository('AppBundle:Place')
                                    ->createQueryBuilder('p')
                                    ->orderBy('p.name')
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getData();

        $choices = array();
        foreach ($view->vars['choices'] as $choice) {
            if (null !== $data && $data->getId() === $choice->data->getId()) {
                $choices[] = $choice;
            }
        }

        $view->vars['choices'] = $choices;
    }
}
