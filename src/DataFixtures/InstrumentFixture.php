<?php

namespace App\DataFixtures;

use App\Entity\Asset;
use App\Entity\Instrument;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InstrumentFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            AssetFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $asset_manager = $manager->getRepository(Asset::class);

        $appl = $asset_manager->findOneBy(['ISIN' => 'US0378331005']);

        $appl_inst = new Instrument();
        $appl_inst->setName("Apple Mini-Future Long");
        $appl_inst->setISIN("DE000GX33NN1");
        $appl_inst->setInstrumentClass(Instrument::CLASS_KNOCKOUT);
        $appl_inst->setCurrency("EUR");
        $appl_inst->setUnderlying($appl);
        $manager->persist($appl_inst);

        $msft = $asset_manager->findOneBy(['ISIN' => 'US5949181045']);

        $msft_inst = new Instrument();
        $msft_inst->setName("Microsoft Corp.");
        $msft_inst->setISIN("US5949181045");
        $msft_inst->setInstrumentClass(Instrument::CLASS_UNDERLYING);
        $msft_inst->setCurrency("USD");
        $msft_inst->setUnderlying($msft);
        $manager->persist($msft_inst);

        $manager->flush();
    }
}
