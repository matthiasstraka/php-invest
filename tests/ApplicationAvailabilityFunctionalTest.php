<?php
namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @dataProvider publicUrlProvider
     */
    public function testPublicPageIsSuccessful($url)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Login', $crawler->filter('#mobile-nav')->siblings()->last()->text());
    }

    public function publicUrlProvider()
    {
        //yield ['/'];
        yield ['/assets'];
        yield ['/instruments'];
        yield ['/country'];
        yield ['/currency'];
        yield ['/register'];
    }

    /**
     * @dataProvider publicLoginUrlProvider
     */
    public function testPublicPageRequestsLogin($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseRedirects("http://localhost/login");
    }

    public function publicLoginUrlProvider()
    {
        yield ['/accounts'];
        yield ['/account/new'];
        yield ['/assets/new'];
        yield ['/assets/edit/1'];
        yield ['/instruments/new'];
        yield ['/country/new'];
        yield ['/currency/new'];
    }

    /**
     * @dataProvider userUrlProvider
     */
    public function testUserPageIsSuccessful($url)
    {
        $client = self::createClient();
        
        $userRepository = static::$container->get(UserRepository::class);
        $demo_user = $userRepository->findOneByEmail('demo@mail.com');
        $this->assertIsObject($demo_user);
        $this->assertEquals($demo_user->getName(), "Demo User");
        
        $client->loginUser($demo_user);
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Demo User Logout', $crawler->filter('#mobile-nav')->siblings()->last()->text());
    }

    public function userUrlProvider()
    {
        yield ['/'];
        yield ['/accounts'];
        yield ['/account/new'];
        yield ['/assets'];
        yield ['/assets/new'];
        yield ['/execution/new'];
        yield ['/instruments'];
        yield ['/instruments/new'];
        yield ['/country'];
        yield ['/currency'];
    }
}
