<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Service\AccountManager;
use App\Service\FileManager\FileManagerInterface;
use App\Service\HttpClient\GeoHttpClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AccountManagerTest extends TestCase
{
    /**
     * @var AccountRepository|MockObject
     */
    private $accountRepository;

    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var GeoHttpClient|MockObject
     */
    private $geoHttpClient;

    /**
     * @var AccountManager|MockObject
     */
    private $accountManager;

    /**
     * @var FileManagerInterface|MockObject
     */
    private $fileManager;

    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        $this->geoHttpClient = $this->createMock(GeoHttpClient::class);
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->entityManager = $this->getEntityManager($this->accountRepository);

        $this->request = new Request();
        $this->request->headers->add([
            'Accept-Language' => 'en',
        ]);
        $requestStack = $this->getRequestStack($this->request);

        $this->accountManager = new AccountManager(
            $this->entityManager,
            $this->geoHttpClient,
            $requestStack,
            $this->fileManager
        );
    }

    public function testFindAccountBy(): void
    {
        $account = $this->getAccount(null, null, null);
        $criteria = [
            'id' => $account->getId(),
        ];

        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($account)
        ;

        $this->assertEquals($account, $this->accountManager->findAccountBy($criteria));
    }

    public function testIsAccountExistBy(): void
    {
        $criteria = [
            'id' => 1,
        ];

        $this->accountRepository
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn(1)
        ;

        $this->assertTrue($this->accountManager->isExistsAccountBy($criteria));
    }

    public function testFindAccounts(): void
    {
        $account = $this->getAccount(null, null, null);
        $accounts = [$account];

        $this->accountRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($accounts)
        ;

        $this->assertEquals($accounts, $this->accountManager->findAccounts());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateDTO(): void
    {
        $account = $this->getAccount('RU', 524894, 'avatar_url');

        $this->createGetCountryMethod($account);
        $this->createGetCityMethod($account);
        $this->createGetPresignedUrlMethod($account);

        $accountDTO = $this->accountManager
            ->createDTO($account)
            ->toArray()
        ;

        $this->assertArrayHasKey('country', $accountDTO);
        $this->assertArrayHasKey('city', $accountDTO);
        $this->assertArrayHasKey('avatar_url', $accountDTO);

        $this->assertSame($account->getCountryId(), $accountDTO['country']['id']);
        $this->assertSame($account->getCityId(), $accountDTO['city']['id']);
        $this->assertSame('presigned_avatar_url', $accountDTO['avatar_url']);
    }

    /**
     * @param string|null $countryId
     * @param int|null $cityId
     * @param string|null $avatarUrl
     *
     * @return Account|MockObject
     */
    public function getAccount(?string $countryId, ?int $cityId, ?string $avatarUrl): MockObject
    {
        /** @var Account|MockObject $account */
        $account = $this->createMock(Account::class);
        $account
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        $authCredentials = new PersistentCollection(
            $this->entityManager,
            new ClassMetadata(Account::class),
            new ArrayCollection()
        );
        $account
            ->expects($this->any())
            ->method('getAuthCredentials')
            ->willReturn($authCredentials)
        ;

        if (null !== $countryId) {
            $account
                ->expects($this->any())
                ->method('getCountryId')
                ->willReturn($countryId)
            ;
        }

        if (null !== $cityId) {
            $account
                ->expects($this->any())
                ->method('getCityId')
                ->willReturn($cityId)
            ;
        }

        if (null !== $avatarUrl) {
            $account
                ->expects($this->any())
                ->method('getAvatarUrl')
                ->willReturn($avatarUrl)
            ;
        }

        return $account;
    }

    /**
     * @param Account|MockObject $account
     */
    private function createGetCountryMethod(MockObject $account): void
    {
        $this->geoHttpClient
            ->expects($this->once())
            ->method('getCountry')
            ->with($account->getCountryId(), $this->request->getPreferredLanguage())
            ->willReturn([
                'id' => $account->getCountryId(),
            ])
        ;
    }

    /**
     * @param Account|MockObject $account
     */
    private function createGetCityMethod(MockObject $account): void
    {
        $this->geoHttpClient
            ->expects($this->once())
            ->method('getCity')
            ->with($account->getCityId(), $this->request->getPreferredLanguage())
            ->willReturn([
                'id' => $account->getCityId(),
            ])
        ;
    }

    /**
     * @param Account|MockObject $account
     */
    private function createGetPresignedUrlMethod(MockObject $account): void
    {
        $this->fileManager
            ->expects($this->once())
            ->method('getPresignedUrl')
            ->with($account->getAvatarUrl())
            ->willReturn('presigned_avatar_url')
        ;
    }

    /**
     * @param AccountRepository|MockObject $accountRepository
     *
     * @return EntityManagerInterface|MockObject
     */
    private function getEntityManager(MockObject $accountRepository): MockObject
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(Account::class)
            ->willReturn($accountRepository)
        ;

        return $entityManager;
    }

    /**
     * @param Request $request
     *
     * @return RequestStack|MockObject
     */
    private function getRequestStack(Request $request): MockObject
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request)
        ;

        return $requestStack;
    }
}
