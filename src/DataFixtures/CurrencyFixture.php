<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['seeder'];
    }
    
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Currency("USD"));
        $manager->persist(new Currency("EUR", "EU0009652759"));
        $manager->flush();
    }
}
