<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CountriesWebTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/country');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration of countries');
    }
}
