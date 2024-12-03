<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixture extends Fixture implements DependentFixtureInterface
{
    public const DEMO_ACCOUNT_REFERENCE = 'demo-account';

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $account = new Account();
        $account->setName("Demo Account");
        $account->setNumber("112358");
        $account->setCurrency("USD");
        $account->setTimezone("Europe/Berlin");
        $account->setOwner($this->getReference(UserFixture::DEMO_USER_REFERENCE, User::class));
        $manager->persist($account);

        $manager->flush();

        $this->addReference(self::DEMO_ACCOUNT_REFERENCE, $account);
    }
}
