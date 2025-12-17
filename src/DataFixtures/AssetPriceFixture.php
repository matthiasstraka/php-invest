<?php

namespace App\DataFixtures;

use App\Entity\Asset;
use App\Entity\AssetPrice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AssetPriceFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['seeder'];
    }

    public function load(ObjectManager $manager): void
    {
        $asset_manager = $manager->getRepository(Asset::class);

        $eurusd = $asset_manager->findOneBy(['ISIN' => 'EU0009652759']);
        $p = new AssetPrice();
        $p->setAsset($eurusd);
        $p->setDate(new \DateTime('2022-05-31'));
        $p->setOHLC(1.0780, 1.0785, 1.0681, 1.0735);
        $manager->persist($p);

        $p = new AssetPrice();
        $p->setAsset($eurusd);
        $p->setDate(new \DateTime('2022-05-30'));
        $p->setOHLC(1.0730, 1.0789, 1.0726, 1.0781);
        $manager->persist($p);

        $manager->flush();
    }
}
