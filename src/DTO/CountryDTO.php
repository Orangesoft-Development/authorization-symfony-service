<?php


namespace App\DTO;


class CountryDTO
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
     * CityDTO constructor.
     *
     * @param array $country
     */
    public function __construct(array $country)
    {
        $this->id = $country['id'];
        $this->name = $country['name'] ?? '';
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
