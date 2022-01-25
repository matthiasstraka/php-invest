<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Country("AT"));
        $manager->persist(new Country("DE"));
        $manager->persist(new Country("US"));
        $manager->flush();
    }
}
