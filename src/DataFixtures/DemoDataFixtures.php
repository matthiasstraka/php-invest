<?php

namespace App\DataFixtures;

use App\DataFixtures\StaticDataFixtures;
use App\Entity\Asset;
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

    public function importAssets(ObjectManager $manager, int $type, string $filename)
    {
        $file = fopen($filename, 'r');
        $header = fgetcsv($file);
        while (($data = fgetcsv($file)))
        {
            $c = new Asset();
            $c->setISIN($data[0]);
            $c->setName($data[1]);
            $c->setSymbol($data[2]);
            $c->setType($type);
            $c->setCurrency($data[3]);
            $manager->persist($c);
        }
        fclose($file);
    }

    public function load(ObjectManager $manager)
    {
        $datadir = dirname(__DIR__, 2) . '/data/';
        $this->importAssets($manager, Asset::TYPE_COMMODITY, $datadir . 'asset_commodities.csv');

        $appl = new Asset();
        $appl->setName("Apple Inc.");
        $appl->setISIN("US0378331005");
        $appl->setSymbol("AAPL");
        $appl->setType(Asset::TYPE_STOCK);
        $appl->setCurrency("USD");
        $appl->setCountry("US");
        $manager->persist($appl);

        $msft = new Asset();
        $msft->setName("Microsoft Corp.");
        $msft->setISIN("US5949181045");
        $msft->setSymbol("MSFT");
        $msft->setType(Asset::TYPE_STOCK);
        $msft->setCurrency("USD");
        $msft->setCountry("US");
        $manager->persist($msft);

        $this->importYahooData($manager, $appl, $datadir . 'yahoo/AAPL.csv');
        $this->importYahooData($manager, $msft, $datadir . 'yahoo/MSFT.csv');

        $sie = new Asset();
        $sie->setName("Siemens AG");
        $sie->setISIN("DE0007236101");
        $sie->setSymbol("SIE");
        $sie->setType(Asset::TYPE_STOCK);
        $sie->setCurrency("EUR");
        $sie->setCountry("DE");
        $manager->persist($sie);

        $manager->flush();
    }
}
