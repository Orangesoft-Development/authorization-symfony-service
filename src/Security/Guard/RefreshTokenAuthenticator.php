<?php

namespace App\Security\Guard;

use App\Entity\Account;
use App\Security\Handler\AuthenticationFailureHandler;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AccountManager;
use App\Service\AuthCredentialManager;
use App\Service\JWTManager;
use App\Service\SessionManager;
use App\Storage\AuthCredentialStorage;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var JWTManager
     */
    private $jwtManager;

    /**
     * @var AuthCredentialStorage
     */
    private $authCredentialStorage;

    /**
     * RefreshTokenAuthenticator constructor.
     *
     * @param UserCheckerInterface $userChecker
     * @param SessionManager $sessionManager
     * @param JWTManager $jwtManager
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     * @param AuthenticationSuccessHandler $successHandler
     * @param AuthenticationFailureHandler $failureHandler
     */
    public function __construct(
        UserCheckerInterface $userChecker,
        SessionManager $sessionManager,
        JWTManager $jwtManager,
        AccountManager $accountManager,
        AuthCredentialManager $authCredentialManager,
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler
    ) {
        $this->userChecker = $userChecker;
        $this->sessionManager = $sessionManager;
        $this->jwtManager = $jwtManager;
        $this->authCredentialStorage = new AuthCredentialStorage();

        parent::__construct($accountManager, $authCredentialManager, $successHandler, $failureHandler);
    }

    public function supports(Request $request)
    {
        return null !== $request->get('refresh_token');
    }

    public function getCredentials(Request $request)
    {
        return [
            'access_token' => $request->get('access_token'),
            'refresh_token' => $request->get('refresh_token'),
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return Account|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $session = $this->sessionManager->findSessionBy([
            'accessToken' => $credentials['access_token'],
            'refreshToken' => $credentials['refresh_token'],
        ]);

        if (null === $session) {
            throw new AuthenticationException(
                sprintf('Refresh token "%s" does not exist.', $credentials['refresh_token'])
            );
        }

        $account = $session->getAuthCredential()->getAccount();

        $this->authCredentialStorage->setCredential($session->getAuthCredential());
        $this->sessionManager->delete($session);

        $this->userChecker->checkPreAuth($account);
        $this->userChecker->checkPostAuth($account);

        return $account;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $exception = new AuthenticationException('Authentication Required', 0, $authException);

        return new JsonResponse(
            $exception->getMessageKey(),
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return Response|null
     *
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $credential = $this->authCredentialStorage->getCredential();
        $accessToken = $this->jwtManager->create($credential->getAccount());
        $session = $this->sessionManager->create();
        $session
            ->setAccessToken($accessToken)
            ->setAuthCredential($credential)
        ;
        $this->sessionManager->update($session);

        return new JsonResponse([
            'access_token' => $accessToken,
            'refresh_token' => $session->getRefreshToken(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @return AuthCredentialStorage
     */
    public function getAuthCredentialStorage(): AuthCredentialStorage
    {
        return $this->authCredentialStorage;
    }
}
