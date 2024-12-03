<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const DEMO_USER_REFERENCE = 'demo-user';

    public function load(ObjectManager $manager): void
    {
        $demo_user = new User();
        $demo_user->setUsername("demo");
        $demo_user->setPassword("demo_pwd"); // not actually a hash
        $demo_user->setName("Demo User");
        $demo_user->setEmail("demo@mail.com");
        $demo_user->setCurrency("EUR");
        $manager->persist($demo_user);

        $manager->flush();

        $this->addReference(self::DEMO_USER_REFERENCE, $demo_user);
    }
}
