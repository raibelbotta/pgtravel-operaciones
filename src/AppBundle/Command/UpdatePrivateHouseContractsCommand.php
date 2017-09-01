<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\ContractPrivateHouseSeason as Seasson;
use AppBundle\Entity\ContractPrivateHouseFacility as Facility;
use AppBundle\Entity\ContractPrivateHousePrice as Price;
use Carbon\Carbon;

/**
 * Description of UpdatePrivateHouseContractsCommand
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class UpdatePrivateHouseContractsCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('app:upgrade-private-house-contracts');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->upgradeContracts();

        $output->writeln('Done!');
    }

    private function upgradeContracts()
    {
        foreach ($this->getContracts() as $contract) {
            $seasson = new Seasson();
            $seasson
                    ->setName('Unique season')
                    ->setStartAt(new \DateTime('now'))
                    ->setEndAt(Carbon::today()->addYear())
                    ;
            $contract->addPrivateHouseSeasson($seasson);
            $this->getManager()->persist($seasson);

            foreach ($contract->getPrivateHouseServices() as $service) {
                $facility = new Facility();
                $facility
                        ->setName($service->getRoomName())
                        ->setMealPlan($service->getMealPlan())
                        ;
                $contract->addPrivateHouseFacility($facility);
                $this->getManager()->persist($facility);
                
                $price = new Price();
                $price->setValue($service->getPrice());

                $seasson->addPrice($price);
                $facility->addPrice($price);

                $this->getManager()->persist($price);
            }
        }

        $this->getManager()->flush();
    }

    private function getContracts()
    {
        $query = $this->getManager()->createQuery('SELECT c FROM AppBundle:Contract AS c WHERE c.model = :model')
                ->setParameter('model', 'private-house')
                ;

        return $query->getResult();
    }

    /**
     * @return EntityManager
     */
    private function getManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
