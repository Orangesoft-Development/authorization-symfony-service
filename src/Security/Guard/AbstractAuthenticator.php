<?php

namespace App\Security\Guard;

use App\Security\Handler\AuthenticationFailureHandler;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AuthCredentialManager;
use App\Service\AccountManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class AbstractAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * @var AuthCredentialManager
     */
    protected $authCredentialManager;

    /**
     * @var AuthenticationSuccessHandler
     */
    private $successHandler;

    /**
     * @var AuthenticationFailureHandler
     */
    private $failureHandler;

    /**
     * PhoneAuthenticator constructor.
     *
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     * @param AuthenticationSuccessHandler $successHandler
     * @param AuthenticationFailureHandler $failureHandler
     */
    public function __construct(
        AccountManager $accountManager,
        AuthCredentialManager $authCredentialManager,
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler
    ) {
        $this->accountManager = $accountManager;
        $this->authCredentialManager = $authCredentialManager;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse|Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse|Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return JsonResponse|Response|null
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return true;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $account
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $account): bool
    {
        return true;
    }
}
