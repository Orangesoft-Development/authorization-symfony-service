<?php

namespace App\Service\HttpClient;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeoHttpClient
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * GeoHttpClient constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->client = HttpClient::create([
            'base_uri' => $parameterBag->get('service_geo_base_uri'),
        ]);
    }

    /**
     * @param string $countryId
     * @param string $lang
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCountry(string $countryId, string $lang = 'en'): array
    {
        $request = $this->client->request(Request::METHOD_GET, '/countries/' . $countryId, [
            'query' => [
                'preferred_lang' => $lang,
            ],
        ]);

        return $request->toArray();
    }

    /**
     * @param int $cityId
     * @param string $lang
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCity(int $cityId, string $lang): array
    {
        $request = $this->client->request(Request::METHOD_GET, '/cities/' . $cityId, [
            'query' => [
                'preferred_lang' => $lang,
            ],
        ]);

        return $request->toArray();
    }
}
