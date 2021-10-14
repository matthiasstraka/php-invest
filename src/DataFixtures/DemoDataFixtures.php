<?php

namespace App\DataFixtures;

use App\DataFixtures\StaticDataFixtures;
use App\Entity\Asset;
use App\Entity\AssetPrice;
use App\Entity\AssetType;
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

    public function importYahooData(ObjectManager $manager, Asset $asset, string $filename)
    {
        $file = fopen($filename, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $p = new AssetPrice();
            $p->setAsset($asset);
            $p->setDate(\DateTime::createFromFormat('Y-m-d', $data[0]));
            $p->setOHLC($data[1], $data[2], $data[3], $data[4]);
            $p->setVolume($data[6]);
            $manager->persist($p);
        }
        fclose($file);
    }

    public function load(ObjectManager $manager)
    {
        $atype = $manager->getRepository(AssetType::class)->findOneByName('Stock');

        $appl = new Asset();
        $appl->setName("Apple Inc.");
        $appl->setISIN("US0378331005");
        $appl->setSymbol("AAPL");
        $appl->setAssetType($atype);
        $appl->setCurrency("USD");
        $appl->setCountry("US");
        $manager->persist($appl);

        $msft = new Asset();
        $msft->setName("Microsoft Corp.");
        $msft->setISIN("US5949181045");
        $msft->setSymbol("MSFT");
        $msft->setAssetType($atype);
        $msft->setCurrency("USD");
        $msft->setCountry("US");
        $manager->persist($msft);

        $datadir = dirname(__DIR__, 2) . '/data/';
        $this->importYahooData($manager, $appl, $datadir . 'yahoo/AAPL.csv');
        $this->importYahooData($manager, $msft, $datadir . 'yahoo/MSFT.csv');

        $sie = new Asset();
        $sie->setName("Siemens AG");
        $sie->setISIN("DE0007236101");
        $sie->setSymbol("SIE");
        $sie->setAssetType($atype);
        $sie->setCurrency("EUR");
        $sie->setCountry("DE");
        $manager->persist($sie);

        $manager->flush();
    }
}
