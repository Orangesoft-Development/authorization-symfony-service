<?php

namespace App\Tests\Security\Handler;

use App\DBAL\Types\AuthMethodType;
use App\DTO\AccountDTO;
use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Entity\Session;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AccountManager;
use App\Service\AuthCredentialManager;
use App\Service\JWTManager;
use App\Service\SessionManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthenticationSuccessHandlerTest extends TestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testOnAuthenticationSuccess(): void
    {
        $token = 'access_token';
        $jwtManager = $this->getJWTManager($token);

        $session = new Session();
        $session->setRefreshToken('refresh_token');
        $sessionManager = $this->getSessionManager($session);

        $account = $this->getAccount();
        $accountManager = $this->getAccountManager($account);

        $credential = $account->getAuthCredentials()[0];
        $credentialManager = $this->getCredentialManager($credential);

        $parameterBag = new ParameterBag([
            'jwt_authentication' => [
                'refresh_token_ttl' => 3600,
            ],
        ]);

        $authenticationFailureHandler = new AuthenticationSuccessHandler(
            $jwtManager,
            $sessionManager,
            $accountManager,
            $credentialManager,
            $parameterBag
        );

        $request = new Request([], [
            'method' => $credential->getMethod(),
        ]);

        $credentialsToken = $this->getCredentialsToken($account);

        $response = $authenticationFailureHandler->onAuthenticationSuccess($request, $credentialsToken);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('auth_tokens', $content);
        $this->assertArrayHasKey('access_token', $content['auth_tokens']);
        $this->assertArrayHasKey('refresh_token', $content['auth_tokens']);

        $this->assertEquals($token, $content['auth_tokens']['access_token']);
        $this->assertEquals($session->getRefreshToken(), $content['auth_tokens']['refresh_token']);

        $this->assertArrayHasKey('account', $content);
        $this->assertArrayHasKey('id', $content['account']);
        $this->assertEquals($account->getId(), $content['account']['id']);
    }

    /**
     * @param string $token
     *
     * @return JWTManager|MockObject
     */
    private function getJWTManager(string $token): MockObject
    {
        /** @var JWTManager|MockObject $jwtManager */
        $jwtManager = $this->createMock(JWTManager::class);
        $jwtManager
            ->expects($this->any())
            ->method('create')
            ->willReturn($token)
        ;

        return $jwtManager;
    }

    /**
     * @param Session $session
     *
     * @return SessionManager|MockObject
     */
    private function getSessionManager(Session $session): MockObject
    {
        /** @var SessionManager|MockObject $sessionManager */
        $sessionManager = $this->createMock(SessionManager::class);
        $sessionManager
            ->expects($this->any())
            ->method('create')
            ->willReturn($session)
        ;

        $sessionManager
            ->expects($this->any())
            ->method('update')
            ->with($session)
        ;

        return $sessionManager;
    }

    /**
     * @param Account|MockObject $account
     *
     * @return AccountManager|MockObject
     */
    private function getAccountManager(MockObject $account): MockObject
    {
        $accountDTO = new AccountDTO($account);

        /** @var AccountManager|MockObject $accountManager */
        $accountManager = $this->createMock(AccountManager::class);
        $accountManager
            ->expects($this->any())
            ->method('createDTO')
            ->with($account)
            ->willReturn($accountDTO)
        ;

        return $accountManager;
    }

    /**
     * @param AuthCredential $credential
     *
     * @return AuthCredentialManager|MockObject
     */
    private function getCredentialManager(AuthCredential $credential): MockObject
    {
        /** @var AuthCredentialManager|MockObject $credentialManager */
        $credentialManager = $this->createMock(AuthCredentialManager::class);
        $credentialManager
            ->expects($this->once())
            ->method('findCredentialBy')
            ->with([
                'method' => $credential->getMethod(),
                'account' => $credential->getAccount()->getId(),
            ])
            ->willReturn($credential)
        ;

        return $credentialManager;
    }

    /**
     * @param Account|MockObject $account
     *
     * @return TokenInterface|MockObject
     */
    private function getCredentialsToken(MockObject $account): MockObject
    {
        /** @var TokenInterface|MockObject $credentialsToken */
        $credentialsToken = $this->createMock(TokenInterface::class);
        $credentialsToken
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($account)
        ;

        return $credentialsToken;
    }

    /**
     * @return Account|MockObject
     */
    private function getAccount(): MockObject
    {
        /** @var Account|MockObject $account */
        $account = $this->createMock(Account::class);
        $account
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        /** @var EntityManagerInterface|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $credential = new AuthCredential();
        $credential
            ->setMethod(AuthMethodType::PHONE)
            ->setUsername('phone')
            ->setName('phone')
            ->setAccount($account)
        ;

        $authCredentials = new PersistentCollection(
            $entityManager,
            new ClassMetadata(Account::class),
            new ArrayCollection([$credential])
        );
        $account
            ->expects($this->any())
            ->method('getAuthCredentials')
            ->willReturn($authCredentials)
        ;

        return $account;
    }
}
