<?php

namespace App\Tests\Security\Guard;

use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Entity\Session;
use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\RefreshTokenAuthenticator;
use App\Service\JWTManager;
use App\Service\SessionManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RefreshTokenAuthenticatorTest extends AbstractAuthenticatorTest
{
    /**
     * @var UserCheckerInterface|MockObject
     */
    private $userChecker;

    /**
     * @var SessionManager|MockObject
     */
    private $sessionManager;

    /**
     * @var JWTManager|MockObject
     */
    private $jwtManager;

    /**
     * @var RefreshTokenAuthenticator
     */
    protected $authenticator;

    protected function setUp(): void
    {
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->jwtManager = $this->createMock(JWTManager::class);

        parent::setUp();
    }

    public function testSupports(): void
    {
        $request = new Request([], [
            'refresh_token' => 'refresh_token',
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->getCredentials();
        $request = new Request([], $credentials);

        $this->assertEquals($credentials, $this->authenticator->getCredentials($request));
    }

    public function testStart(): void
    {
        $response = $this->authenticator->start(new Request(), new AuthenticationException());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testGetUserWithExistsSession(): void
    {
        $credentials = $this->getCredentials();

        $account = new Account();

        $authCredential = new AuthCredential();
        $authCredential->setAccount($account);

        $session = new Session();
        $session->setAuthCredential($authCredential);

        $this->sessionManager
            ->expects($this->once())
            ->method('findSessionBy')
            ->with([
                'accessToken' => $credentials['access_token'],
                'refreshToken' => $credentials['refresh_token'],
            ])
            ->willReturn($session)
        ;

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);

        $actualAccount = $this->authenticator->getUser($credentials, $userProvider);
        $actualAuthCredential = $this->authenticator
            ->getAuthCredentialStorage()
            ->getCredential()
        ;

        $this->assertEquals($account, $actualAccount);
        $this->assertEquals($authCredential, $actualAuthCredential);
    }

    public function testGetUserWithNotExistsSession(): void
    {
        $credentials = $this->getCredentials();

        $this->sessionManager
            ->expects($this->once())
            ->method('findSessionBy')
            ->with([
                'accessToken' => $credentials['access_token'],
                'refreshToken' => $credentials['refresh_token'],
            ])
            ->willReturn(null)
        ;

        $this->expectException(AuthenticationException::class);

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);
        $this->authenticator->getUser($credentials, $userProvider);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $account = new Account();

        $authCredential = new AuthCredential();
        $authCredential->setAccount($account);

        $this->authenticator
            ->getAuthCredentialStorage()
            ->setCredential($authCredential)
        ;

        $accessToken = 'encoded_access_token';
        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($account)
            ->willReturn($accessToken)
        ;

        $session = new Session();
        $session->setRefreshToken('encoded_refresh_token');

        $this->sessionManager
            ->expects($this->once())
            ->method('create')
            ->willReturn($session)
        ;

        /** @var TokenInterface|MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $response = $this->authenticator->onAuthenticationSuccess(new Request(), $token, 'provider_key');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertSame($accessToken, $content['access_token']);
        $this->assertSame($session->getRefreshToken(), $content['refresh_token']);
    }

    /**
     * @return AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new RefreshTokenAuthenticator(
            $this->userChecker,
            $this->sessionManager,
            $this->jwtManager,
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }

    /**
     * @return array|string[]
     */
    protected function getCredentials()
    {
        return [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
        ];
    }
}
