<?php

namespace App\Tests\Security\Guard;

use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\DefaultAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DefaultAuthenticatorTest extends AbstractAuthenticatorTest
{
    /**
     * @var DefaultAuthenticator
     */
    protected $authenticator;

    public function testSupports(): void
    {
        $request = new Request([], [
            'method' => 'undefined_method',
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testGetCredentials(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->authenticator->getCredentials(new Request());
    }

    public function testGetUser(): void
    {
        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);

        $this->assertNull($this->authenticator->getUser([], $userProvider));
    }

    /**
     * @return DefaultAuthenticator|AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new DefaultAuthenticator(
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }
}
