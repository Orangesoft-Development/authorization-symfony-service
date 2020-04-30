<?php

namespace App\Tests\Service\HttpClient;

use App\Service\HttpClient\GeoHttpClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeoHttpClientTest extends KernelTestCase
{
    /**
     * @var GeoHttpClient
     */
    private $geoHttpClient;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->geoHttpClient = self::$container->get(GeoHttpClient::class);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCountry(): void
    {
        // RU - Russia ID
        $country = $this->geoHttpClient->getCountry('RU', 'en');

        $this->assertSame('RU', $country['id']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCity(): void
    {
        $cityId = 524894; // 524894 - Moscow ID
        $city = $this->geoHttpClient->getCity($cityId, 'en');

        $this->assertSame($cityId, $city['id']);
    }
}
