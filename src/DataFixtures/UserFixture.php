<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $demo_user = new User();
        $demo_user->setUsername("demo");
        $demo_user->setPassword("demo_pwd"); // not actually a hash
        $demo_user->setName("Demo User");
        $demo_user->setEmail("demo@mail.com");
        $manager->persist($demo_user);

        $manager->flush();
    }
}
