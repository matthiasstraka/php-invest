<?php
namespace App\Tests;

use App\Repository\AssetRepository;
use App\Repository\InstrumentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    private function resolveUrl($url)
    {
        $asset_manager = static::getContainer()->get(AssetRepository::class);
        $instrument_manager = static::getContainer()->get(InstrumentRepository::class);
        $aapl = $asset_manager->findOneBy(['ISIN' => 'US0378331005']);
        $msft = $instrument_manager->findOneBy(['isin' => 'US5949181045']);

        $url = str_replace('<asset>', $aapl->getId(), $url);
        $url = str_replace('<instrument>', $msft->getId(), $url);
        return $url;
    }

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

        $url = $this->resolveUrl($url);

        $client->request('GET', $url);

        $this->assertResponseRedirects("http://localhost/login");
    }

    public function publicLoginUrlProvider()
    {
        yield ['/accounts'];
        yield ['/account/new'];
        yield ['/asset/new'];
        yield ['/asset/<asset>'];
        yield ['/asset/edit/<asset>'];
        yield ['/instrument/new'];
        yield ['/instrument/edit/<instrument>'];
        yield ['/country/new'];
        yield ['/currency/new'];
    }

    /**
     * @dataProvider userUrlProvider
     */
    public function testUserPageIsSuccessful($url)
    {
        $client = self::createClient();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $demo_user = $userRepository->findOneByEmail('demo@mail.com');
        $this->assertIsObject($demo_user);

        $url = $this->resolveUrl($url);

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
        yield ['/asset/new'];
        yield ['/asset/<asset>'];
        yield ['/asset/edit/<asset>'];
        yield ['/execution/new?instrument=<instrument>'];
        yield ['/instruments'];
        yield ['/instrument/new'];
        yield ['/instrument/<instrument>'];
        yield ['/instrument/edit/<instrument>'];
        yield ['/country'];
        yield ['/currency'];
    }
}
