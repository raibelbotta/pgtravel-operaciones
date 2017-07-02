<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Province;

class LoadProvinceData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getProvinces() as $p) {
            $record = new Province();
            $record->setName($p);

            $manager->persist($record);
        }

        $manager->flush();
    }

    private function getProvinces()
    {
        return array(
            'Pinar del Río',
            'La Habana',
            'Mayabeque',
            'Artemisa',
            'Matanzas',
            'Villa Clara',
            'Cienfuegos',
            'Sancti Spiritus',
            'Camagüey',
            'Ciego de Ávila',
            'Las Tunas',
            'Granma',
            'Santiago de Cuba',
            'Holguín',
            'Guantánamo',
            'Isla de la Juventud'
        );
    }
}
