<?php

namespace App\Security\Handler;

use App\Entity\Account;
use App\Service\AccountManager;
use App\Service\AuthCredentialManager;
use App\Service\JWTManager;
use App\Service\SessionManager;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var JWTManager
     */
    protected $jwtManager;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var AuthCredentialManager
     */
    protected $credentialManager;

    /**
     * @var int
     */
    protected $refreshTokenTtl;

    /**
     * AuthenticationSuccessHandler constructor.
     *
     * @param JWTManager $jwtManager
     * @param SessionManager $sessionManager
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $credentialManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        JWTManager $jwtManager,
        SessionManager $sessionManager,
        AccountManager $accountManager,
        AuthCredentialManager $credentialManager,
        ParameterBagInterface $parameterBag
    ) {
        $this->jwtManager = $jwtManager;
        $this->sessionManager = $sessionManager;
        $this->accountManager = $accountManager;
        $this->credentialManager = $credentialManager;
        $this->refreshTokenTtl = $parameterBag->get('jwt_authentication')['refresh_token_ttl'];
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     *
     * @return JsonResponse|Response
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var Account $account */
        $account = $token->getUser();

        $credential = $this->credentialManager->findCredentialBy([
            'method' => $request->get('method'),
            'account' => $account->getId(),
        ]);

        $accessToken = $this->jwtManager->create($account);
        $session = $this->sessionManager->create();
        $session
            ->setAccessToken($accessToken)
            ->setAuthCredential($credential)
        ;
        $this->sessionManager->update($session);

        $accountDTO = $this->accountManager->createDTO($account);

        return new JsonResponse([
            'auth_tokens' => [
                'access_token' => $accessToken,
                'refresh_token' => $session->getRefreshToken(),
            ],
            'account' => $accountDTO->toArray(),
        ], JsonResponse::HTTP_OK);
    }
}
