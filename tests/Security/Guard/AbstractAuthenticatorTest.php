<?php

namespace App\Tests\Security\Guard;

use App\Security\Guard\AbstractAuthenticator;
use App\Security\Handler\AuthenticationFailureHandler;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AccountManager;
use App\Service\AuthCredentialManager;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractAuthenticatorTest extends TestCase
{
    /**
     * @var AccountManager|MockObject
     */
    protected $accountManager;

    /**
     * @var AuthCredentialManager|MockObject
     */
    protected $authCredentialManager;

    /**
     * @var AuthenticationSuccessHandler|MockObject
     */
    protected $successHandler;

    /**
     * @var AuthenticationFailureHandler|MockObject
     */
    protected $failureHandler;

    /**
     * @var AbstractAuthenticator
     */
    protected $authenticator;

    protected function setUp(): void
    {
        $this->accountManager = $this->createMock(AccountManager::class);
        $this->authCredentialManager = $this->createMock(AuthCredentialManager::class);
        $this->successHandler = $this->createMock(AuthenticationSuccessHandler::class);
        $this->failureHandler = $this->createMock(AuthenticationFailureHandler::class);

        $this->authenticator = $this->getAuthenticator();
    }

    public function testStart(): void
    {
        $response = $this->authenticator->start(new Request(), new AuthenticationException());

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = new Request();
        $exception = new AuthenticationException();
        $response = new JsonResponse();

        $this->failureHandler
            ->expects($this->any())
            ->method('onAuthenticationFailure')
            ->with($request, $exception)
            ->willReturn($response)
        ;

        $this->assertEquals($response, $this->authenticator->onAuthenticationFailure($request, $exception));
    }

    /**
     * @throws Exception
     */
    public function testOnAuthenticationSuccess(): void
    {
        $request = new Request();
        /** @var TokenInterface|MockObject $credentialsToken */
        $credentialsToken = $this->createMock(TokenInterface::class);
        $providerKey = 'provider_key';
        $response = new JsonResponse();

        $this->successHandler
            ->expects($this->any())
            ->method('onAuthenticationSuccess')
            ->with($request, $credentialsToken)
            ->willReturn($response)
        ;

        $actualResponse = $this->authenticator->onAuthenticationSuccess($request, $credentialsToken, $providerKey);

        $this->assertEquals($response, $actualResponse);
    }

    public function testSupportsRememberMe(): void
    {
        $this->assertTrue($this->authenticator->supportsRememberMe());
    }

    public function testCheckCredentials(): void
    {
        /** @var UserInterface|MockObject $account */
        $account = $this->createMock(UserInterface::class);

        $credentials = $this->getCredentials();

        $this->assertTrue($this->authenticator->checkCredentials($credentials, $account));
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        return [];
    }

    /**
     * @return AbstractAuthenticator
     */
    abstract protected function getAuthenticator(): AbstractAuthenticator;
}
