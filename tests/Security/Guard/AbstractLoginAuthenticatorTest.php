<?php

namespace App\Tests\Security\Guard;

use App\Entity\Account;
use App\Security\Guard\AbstractLoginAuthenticator;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class AbstractLoginAuthenticatorTest extends AbstractAuthenticatorTest
{
    /**
     * @var AbstractLoginAuthenticator
     */
    protected $authenticator;

    public function testGetUserWithExistsAccount(): void
    {
        $account = new Account();
        $credentials = $this->getCredentials();
        $this->createAuthCredential($credentials, $account);

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);

        $this->assertEquals($account, $this->authenticator->getUser($credentials, $userProvider));
    }

    public function testGetUserWithNotExistsAccount(): void
    {
        $credentials = $this->getCredentials();
        $this->createAuthCredential($credentials, null);

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);

        $account = new Account();
        $this->accountManager
            ->expects($this->once())
            ->method('create')
            ->willReturn($account)
        ;

        $this->assertEquals($account, $this->authenticator->getUser($credentials, $userProvider));
    }

    /**
     * @param array|AccessToken $credentials
     * @param Account|null $account
     */
    abstract protected function createAuthCredential($credentials, ?Account $account);
}
