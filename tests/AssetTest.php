<?php
namespace App\Tests;

use App\Repository\AssetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssetTest extends WebTestCase
{
    public function testShowAsset()
    {        
        $client = self::createClient();
        $user_manager = static::getContainer()->get(UserRepository::class);
        $asset_manager = static::getContainer()->get(AssetRepository::class);

        $appl = $asset_manager->findOneBy(['ISIN' => 'US0378331005']);
        $demo_user = $user_manager->findOneByEmail('demo@mail.com');
        
        $client->loginUser($demo_user);
        $crawler = $client->request('GET', '/asset/' . $appl->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSame('SymbolAAPL', $crawler->filter('dd')->text());
    }
}
