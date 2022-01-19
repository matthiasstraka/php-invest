<?php
namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AssetTest extends WebTestCase
{
    public function testShowAsset()
    {        
        $client = self::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $demo_user = $userRepository->findOneByEmail('demo@mail.com');
        
        $client->loginUser($demo_user);
        $crawler = $client->request('GET', '/asset/1');

        $this->assertResponseIsSuccessful();
        $this->assertSame('SymbolXAU', $crawler->filter('dd')->text());
    }
}
