<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function urlProvider()
    {
        yield ['/'];
        yield ['/assets'];
        yield ['/assets/new'];
        yield ['/instruments'];
        yield ['/instruments/new'];
        yield ['/country'];
        yield ['/country/new'];
        yield ['/currency'];
        yield ['/currency/new'];
    }
}
