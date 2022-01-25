<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Currency("USD"));
        $manager->persist(new Currency("EUR"));
        $manager->flush();
    }
}
