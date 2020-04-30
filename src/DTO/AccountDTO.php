<?php

namespace App\DTO;

use App\Entity\AuthCredential;
use App\Entity\Account;

class AccountDTO
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $avatarUrl;

    /**
     * @var string|null
     */
    private $birthday;

    /**
     * @var CountryDTO
     */
    private $country;

    /**
     * @var CityDTO
     */
    private $city;

    /**
     * @var AuthCredentialDTO[]
     */
    private $authCredentials;

    /**
     * AccountDTO constructor.
     *
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->id = $account->getId();
        $this->name = $account->getName();
        $this->birthday = $account->getBirthday() ? $account->getBirthday()->format('Y-m-d') : null;
        $this->authCredentials = $account->getAuthCredentials()
            ->map(function (AuthCredential $credential) {
                return new AuthCredentialDTO($credential);
            })
            ->getValues()
        ;
    }

    /**
     * @param string|null $avatarUrl
     *
     * @return $this
     */
    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * @param CountryDTO $country
     *
     * @return $this
     */
    public function setCountry(CountryDTO $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param CityDTO $cityDTO
     *
     * @return $this
     */
    public function setCity(CityDTO $cityDTO): self
    {
        $this->city = $cityDTO;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'avatar_url' => $this->avatarUrl,
            'birthday' => $this->birthday,
            'country' => $this->country ? $this->country->toArray() : null,
            'city' => $this->city ? $this->city->toArray() : null,
            'auth_credentials' => array_map(function (AuthCredentialDTO $credentialDTO) {
                return $credentialDTO->toArray();
            }, $this->authCredentials),
        ]);
    }
}
