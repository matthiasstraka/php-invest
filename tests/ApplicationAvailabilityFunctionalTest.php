<?php
namespace App\Tests;

use App\Repository\AccountRepository;
use App\Repository\AssetRepository;
use App\Repository\InstrumentRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    private function resolveUrl($url)
    {
        if (str_contains($url, '<asset>'))
        {
            $asset_manager = static::getContainer()->get(AssetRepository::class);
            $aapl = $asset_manager->findOneBy(['ISIN' => 'US0378331005']);
            $this->assertIsObject($aapl);
            $url = str_replace('<asset>', $aapl->getId(), $url);
        }
        
        if (str_contains($url, '<instrument>'))
        {
            $instrument_manager = static::getContainer()->get(InstrumentRepository::class);
            $msft = $instrument_manager->findOneBy(['isin' => 'US5949181045']);
            $this->assertIsObject($msft);
            $url = str_replace('<instrument>', $msft->getId(), $url);
        }
        
        if (str_contains($url, '<account>'))
        {
            $account_manager = static::getContainer()->get(AccountRepository::class);
            $account = $account_manager->findOneBy(['name' => 'Demo Account']);
            $this->assertIsObject($account);
            $url = str_replace('<account>', $account->getId(), $url);
        }

        return $url;
    }

    #[DataProvider('publicUrlProvider')]
    public function testPublicPageIsSuccessful($url)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Login', $crawler->filter('#mobile-nav')->siblings()->last()->text());
    }

    public static function publicUrlProvider()
    {
        //yield ['/'];
        yield ['/assets'];
        yield ['/instruments'];
        yield ['/country'];
        yield ['/currency'];
        yield ['/register'];
    }

    #[DataProvider('publicLoginUrlProvider')]
    public function testPublicPageRequestsLogin($url)
    {
        $client = self::createClient();

        $url = $this->resolveUrl($url);

        $client->request('GET', $url);

        $this->assertResponseRedirects("http://localhost/login");
    }

    public static function publicLoginUrlProvider()
    {
        yield ['/accounts'];
        yield ['/account/new'];
        yield ['/analysis'];
        yield ['/asset/new'];
        yield ['/asset/<asset>'];
        yield ['/asset/<asset>/edit'];
        yield ['/assetnote/new'];
        yield ['/instrument/new'];
        yield ['/instrument/<instrument>/edit'];
        yield ['/country/new'];
        yield ['/currency/new'];
    }

    #[DataProvider('userUrlProvider')]
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

    public static function userUrlProvider()
    {
        yield ['/'];
        yield ['/accounts'];
        yield ['/account/new'];
        yield ['/account/<account>/edit'];
        yield ['/account/<account>/positions'];
        yield ['/account/<account>/trades'];
        yield ['/account/<account>/transactions'];
        yield ['/analysis'];
        yield ['/assets'];
        yield ['/asset/new'];
        yield ['/asset/<asset>'];
        yield ['/asset/<asset>/edit'];
        yield ['/assetnote/new'];
        yield ['/assetnote/new?asset=<asset>'];
        yield ['/execution/new?instrument=<instrument>'];
        yield ['/instruments'];
        yield ['/instrument/new'];
        yield ['/instrument/new?underlying=<asset>'];
        yield ['/instrument/<instrument>'];
        yield ['/instrument/<instrument>/edit'];
        yield ['/instrument/<instrument>/terms'];
        yield ['/instrument/<instrument>/terms/new'];
        yield ['/country'];
        yield ['/currency'];
    }
}
