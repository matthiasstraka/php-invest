<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array {
        return ['seeders'];
    }

    public function load(ObjectManager $manager)
    {
        $src = dirname(__DIR__, 2) . '/data/countries.csv';
        $file = fopen($src, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $manager->persist(new Country($data[0], $data[2]));
        }
        fclose($file);

        $manager->flush();
    }
}
