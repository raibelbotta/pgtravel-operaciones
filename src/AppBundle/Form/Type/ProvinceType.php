<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Province;

/**
 * Description of ProvinceType
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ProvinceType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class'         => Province::class,
            'query_builder' => $this->manager->getRepository('AppBundle:Province')
                                    ->createQueryBuilder('p')
                                    ->orderBy('p.name')
        ));
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
