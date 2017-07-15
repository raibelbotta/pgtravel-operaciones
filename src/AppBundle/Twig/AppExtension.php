<?php

namespace AppBundle\Twig;

use Doctrine\ORM\EntityManager;

/**
 * AppExtension
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class AppExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('facility_seasons_ordered', array($this, 'getFacilityOrderedSeasons'))
        );
    }

    public function getFacilityOrderedSeasons(\AppBundle\Entity\ContractFacility $facility)
    {
        $query = $this->manager->createQuery('SELECT s FROM AppBundle:ContractFacilitySeason AS s JOIN s.facility AS f WHERE f.id = :facility ORDER BY s.fromDate')
                ->setParameter('facility', $facility->getId())
                ;

        return $query->getResult();
    }

    public function getName()
    {
        return 'app_extension';
    }
}
