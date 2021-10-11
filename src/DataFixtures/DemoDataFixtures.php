<?php

namespace App\DataFixtures;

use App\DataFixtures\StaticDataFixtures;
use App\Entity\Asset;
use App\Entity\AssetClass;
use App\Entity\AssetPrice;
use App\Entity\Country;
use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DemoDataFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            StaticDataFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $usa = $manager->find(Country::class, 840); // USA
        $usd = $manager->find(Currency::class, 840); // USD
        $aclass = $manager->getRepository(AssetClass::class)->findOneBy(['Name' => 'Stock']);

        $appl = new Asset();
        $appl->setName("Apple Inc.");
        $appl->setISIN("US0378331005");
        $appl->setSymbol("AAPL");
        $appl->setAssetClass($aclass);
        $appl->setCurrency($usd);
        $appl->setCountry($usa);
        $manager->persist($appl);

        $msft = new Asset();
        $msft->setName("Microsoft Corp.");
        $msft->setISIN("US5949181045");
        $msft->setSymbol("MSFT");
        $msft->setAssetClass($aclass);
        $msft->setCurrency($usd);
        $msft->setCountry($usa);
        $manager->persist($msft);

        $p = new AssetPrice();
        $p->setAsset($msft);
        $p->setDate(\DateTime::createFromFormat('Y-m-d', '2021-10-08'));
        $p->setOHLC('296.22', '296.64', '293.76', '294.85');
        $p->setVolume(17680300);
        $manager->persist($p);

        $p = new AssetPrice();
        $p->setAsset($msft);
        $p->setDate(\DateTime::createFromFormat('Y-m-d', '2021-10-09'));
        $p->setOHLC('295.18', '296.64', '293.92', '294.85');
        $p->setVolume(20430500);
        $manager->persist($p);

        $manager->flush();
    }
}
