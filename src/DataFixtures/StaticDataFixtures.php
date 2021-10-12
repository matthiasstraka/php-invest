<?php

namespace App\DataFixtures;

use App\Entity\AssetType;
use App\Entity\Country;
use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class StaticDataFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array {
        return ['seeders'];
    }

    public function seedCountries($manager, $filename)
    {
        $file = fopen($filename, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $manager->persist(new Country(intval($data[0]), $data[1], $data[2]));
        }
        fclose($file);
    }

    public function seedCurrencies($manager, $filename)
    {
        $file = fopen($filename, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $manager->persist(new Currency(intval($data[0]), $data[1], $data[2]));
        }
        fclose($file);
    }

    public function seedAssetType($manager, $filename)
    {
        $file = fopen($filename, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $manager->persist(new AssetType($data[0]));
        }
        fclose($file);
    }

    public function load(ObjectManager $manager)
    {
        $datadir = dirname(__DIR__, 2) . '/data/';
        $this->seedCountries($manager, $datadir . 'countries.csv');
        $this->seedCurrencies($manager, $datadir . 'currencies.csv');
        $this->seedAssetType($manager, $datadir . 'asset_types.csv');

        $manager->flush();
    }
}
