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
        $this->assertSelectorTextContains('h1', 'Currencies');
    }
}
