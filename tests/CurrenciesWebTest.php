<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CurrenciesWebTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/currency');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Currencies');
    }

    public function testApi(): void
    {
        $client = static::createClient();
        $eur = $client->request('GET', '/api/currency/EUR');
        $this->assertResponseIsSuccessful();

        $xxx = $client->request('GET', '/api/currency/XXX');
        $this->assertResponseStatusCodeSame(404);
    }
}
