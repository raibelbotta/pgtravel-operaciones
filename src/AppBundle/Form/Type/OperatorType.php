<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\User;

/**
 * Description of OperatorType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class OperatorType extends AbstractType
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
        $qb = $this->manager->getRepository('AppBundle:User')
                ->createQueryBuilder('u')
                ->orderBy('u.firstName');

        $resolver->setDefaults(array(
            'query_builder' => $qb,
            'class' => User::class,
            'choice_label' => function(User $record) {
                return $record->getFullName();
            }
        ));
    }
}
