<?php

namespace App\DTO;

class CityDTO
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $regionName;

    /**
     * CityDTO constructor.
     *
     * @param array $city
     */
    public function __construct(array $city)
    {
        $this->id = $city['id'];
        $this->name = $city['name'] ?? '';
        $this->regionName = $city['region_name'] ?? '';
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'region_name' => $this->regionName,
        ];
    }
}
