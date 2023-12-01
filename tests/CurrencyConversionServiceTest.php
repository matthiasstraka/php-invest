<?php

namespace App\Tests;

use App\Service\CurrencyConversionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CurrencyConversionServiceTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIdentity(): void
    {
        $cc = static::getContainer()->get(CurrencyConversionService::class);
        $this->assertEquals("1", $cc->latestConversion("USD", "USD"));
    }

    public function testInvalid(): void
    {
        $cc = static::getContainer()->get(CurrencyConversionService::class);
        $this->assertEquals(null, $cc->latestConversion("XXX", "USD"));
        $this->assertEquals(null, $cc->latestConversion("USD", "XXX"));
    }

    public function testDirect(): void
    {
        $cc = static::getContainer()->get(CurrencyConversionService::class);
        $this->assertEquals(1.0735, $cc->latestConversion("EUR", "USD"));
        $this->assertEquals(0.9315, $cc->latestConversion("USD", "EUR"));
    }

    public function testDirectNoPrice(): void
    {
        $cc = static::getContainer()->get(CurrencyConversionService::class);
        $this->assertEquals(null, $cc->latestConversion("GBP", "USD"));
        $this->assertEquals(null, $cc->latestConversion("USD", "GBP"));
    }

    public function testDirectNoAsset(): void
    {
        $cc = static::getContainer()->get(CurrencyConversionService::class);
        $this->assertEquals(null, $cc->latestConversion("AUD", "USD"));
        $this->assertEquals(null, $cc->latestConversion("USD", "AUD"));
    }

    // TODO: Add tests for indirect price conversions (e.g. GBP->EUR)
}
