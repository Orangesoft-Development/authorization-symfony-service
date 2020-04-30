<?php

namespace App\Tests\Functional\src\Service;

use App\Service\HttpClient\GeoHttpClient as BaseGeoHttpClient;

class GeoHttpClient extends BaseGeoHttpClient
{
    /**
     * @param string $countryId
     * @param string $lang
     *
     * @return array
     */
    public function getCountry(string $countryId, string $lang = 'en'): array
    {
        return [
            'id' => $countryId,
        ];
    }

    /**
     * @param int $cityId
     * @param string $lang
     *
     * @return array
     */
    public function getCity(int $cityId, string $lang): array
    {
        return [
            'id' => $cityId,
        ];
    }
}
