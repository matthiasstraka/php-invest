<?php

namespace App\DataFixtures;

use App\Entity\Asset;
use App\Entity\Instrument;
use App\Entity\InstrumentTerms;
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
        $appl_inst->setEusipa(Instrument::EUSIPA_KNOCKOUT);
        $appl_inst->setCurrency("EUR");
        $appl_inst->setUnderlying($appl);
        $manager->persist($appl_inst);

        $appl_terms = new InstrumentTerms();
        $appl_terms->setInstrument($appl_inst);
        $appl_terms->setDate(\DateTime::createFromFormat('Y-m-d', '2022-05-25'));
        $appl_terms->setRatio(0.1);
        $appl_terms->setBarrier(25.808);
        $appl_terms->setStrike(25.1129);
        $manager->persist($appl_terms);

        $msft = $asset_manager->findOneBy(['ISIN' => 'US5949181045']);

        $msft_inst = new Instrument();
        $msft_inst->setName("Microsoft Corp.");
        $msft_inst->setISIN("US5949181045");
        $msft_inst->setEusipa(Instrument::EUSIPA_UNDERLYING);
        $msft_inst->setCurrency("USD");
        $msft_inst->setUnderlying($msft);
        $manager->persist($msft_inst);

        $manager->flush();
    }
}
