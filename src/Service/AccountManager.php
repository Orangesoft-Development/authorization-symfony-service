<?php

namespace App\Service;

use App\DTO\AccountDTO;
use App\DTO\CityDTO;
use App\DTO\CountryDTO;
use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Service\FileManager\FileManagerInterface;
use App\Service\HttpClient\GeoHttpClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AccountManager
{
    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    /**
     * @var GeoHttpClient
     */
    private $geoHttpClient;

    /**
     * @var Request|null
     */
    private $currentRequest;

    /**
     * @var FileManagerInterface
     */
    private $fileManager;

    /**
     * AccountManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param GeoHttpClient $geoHttpClient
     * @param RequestStack $requestStack
     * @param FileManagerInterface $fileManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GeoHttpClient $geoHttpClient,
        RequestStack $requestStack,
        FileManagerInterface $fileManager
    ) {
        $this->objectManager = $entityManager;
        $this->geoHttpClient = $geoHttpClient;
        $this->currentRequest = $requestStack->getCurrentRequest();
        $this->fileManager = $fileManager;
    }

    /**
     * @param Account $account
     */
    public function delete(Account $account): void
    {
        $this->objectManager->remove($account);
        $this->objectManager->flush();
    }

    /**
     * @param array $criteria
     *
     * @return Account|object|null
     */
    public function findAccountBy(array $criteria): ?Account
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function isExistsAccountBy(array $criteria): bool
    {
        return $this->getRepository()->count($criteria) > 0;
    }

    /**
     * @return Account[]|object[]
     */
    public function findAccounts(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param Account $account
     */
    public function reload(Account $account): void
    {
        $this->objectManager->refresh($account);
    }

    /**
     * @param Account $account
     * @param bool $andFlush
     */
    public function update(Account $account, bool $andFlush = true): void
    {
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return AccountRepository|ObjectRepository
     */
    protected function getRepository(): AccountRepository
    {
        return $this->objectManager->getRepository(Account::class);
    }

    /**
     * @return Account
     */
    public function create(): Account
    {
        $account = new Account();

        $this->objectManager->persist($account);

        return $account;
    }

    /**
     * @param Account $account
     *
     * @return AccountDTO
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createDTO(Account $account): AccountDTO
    {
        $accountDTO = new AccountDTO($account);

        $lang = $this->currentRequest->getPreferredLanguage() ?? 'en';

        if ($account->getCountryId()) {
            $country = $this->geoHttpClient->getCountry($account->getCountryId(), $lang);
            $countryDTO = new CountryDTO($country);
            $accountDTO->setCountry($countryDTO);
        }

        if ($account->getCityId()) {
            $city = $this->geoHttpClient->getCity($account->getCityId(), $lang);
            $cityDTO = new CityDTO($city);
            $accountDTO->setCity($cityDTO);
        }

        if ($avatarUrl = $account->getAvatarUrl()) {
            $avatarUrl = $this->fileManager->getPresignedUrl($avatarUrl);
            $accountDTO->setAvatarUrl($avatarUrl);
        }

        return $accountDTO;
    }
}
