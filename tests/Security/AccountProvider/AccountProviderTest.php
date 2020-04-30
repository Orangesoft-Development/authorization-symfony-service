<?php

namespace App\Tests\Security\AccountProvider;

use App\Entity\Account;
use App\Exception\AccountNotFoundException;
use App\Security\AccountProvider\AccountProvider;
use App\Service\AccountManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AccountProviderTest extends TestCase
{
    /**
     * @var AccountManager|MockObject
     */
    private $accountManager;

    /**
     * @var AccountProvider
     */
    private $accountProvider;

    protected function setUp(): void
    {
        $this->accountManager = $this->createMock(AccountManager::class);
        $this->accountProvider = new AccountProvider($this->accountManager);
    }

    public function testLoadAccountByUsername(): void
    {
        $account = $this->getAccount();
        $criteria = [
            'id' => $account->getId(),
        ];
        $this->createFindAccountByMethod($criteria, $account);

        $this->assertEquals($account, $this->accountProvider->loadUserByUsername($account->getId()));
    }

    public function testLoadAccountById(): void
    {
        $account = $this->getAccount();
        $criteria = [
            'id' => $account->getId(),
        ];
        $this->createFindAccountByMethod($criteria, $account);

        $this->assertEquals($account, $this->accountProvider->loadAccountById($account->getId()));
    }

    public function testExceptionLoadAccountById(): void
    {
        $account = $this->getAccount();
        $criteria = [
            'id' => $account->getId(),
        ];
        $this->createFindAccountByMethod($criteria, null);

        $this->expectException(AccountNotFoundException::class);
        $this->accountProvider->loadAccountById($account->getId());
    }

    public function testRefreshUser(): void
    {
        $account = $this->getAccount();
        $criteria = [
            'id' => $account->getId(),
        ];
        $this->createFindAccountByMethod($criteria, $account);

        $this->assertEquals($account, $this->accountProvider->refreshUser($account));
    }

    public function testExceptionRefreshUser(): void
    {
        $account = $this->getAccount();
        $criteria = [
            'id' => $account->getId(),
        ];
        $this->createFindAccountByMethod($criteria, null);

        $this->expectException(AccountNotFoundException::class);
        $this->accountProvider->refreshUser($account);
    }

    /**
     * @param array $criteria
     * @param Account|MockObject|null $account
     */
    private function createFindAccountByMethod(array $criteria, ?MockObject $account): void
    {
        $this->accountManager
            ->expects($this->any())
            ->method('findAccountBy')
            ->with($criteria)
            ->willReturn($account)
        ;
    }

    /**
     * @return Account|MockObject
     */
    private function getAccount(): MockObject
    {
        /** @var Account|MockObject $accountManager */
        $account = $this->createMock(Account::class);
        $account
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        return $account;
    }
}
