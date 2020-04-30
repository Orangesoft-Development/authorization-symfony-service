<?php

namespace App\Security\Guard;

use App\Entity\AuthCredential;
use App\Security\Handler\AuthenticationFailureHandler;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AuthCredentialManager;
use App\Service\AccountManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSocialAuthenticator extends AbstractLoginAuthenticator
{
    /**
     * @var ClientRegistry
     */
    protected $clientRegistry;

    /**
     * AppleIdAuthenticator constructor.
     *
     * @param ClientRegistry $clientRegistry
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     * @param AuthenticationSuccessHandler $successHandler
     * @param AuthenticationFailureHandler $failureHandler
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        AccountManager $accountManager,
        AuthCredentialManager $authCredentialManager,
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler
    ) {
        $this->clientRegistry = $clientRegistry;

        parent::__construct($accountManager, $authCredentialManager, $successHandler, $failureHandler);
    }

    /**
     * @param Request $request
     *
     * @return AccessToken|mixed
     */
    public function getCredentials(Request $request): AccessToken
    {
        return new AccessToken([
            'access_token' => $request->get('access_token'),
        ]);
    }

    /**
     * @param AccessToken $credentials
     *
     * @return AuthCredential
     */
    public function getAuthCredential($credentials): AuthCredential
    {
        $socialUser = $this->getClient()
            ->fetchUserFromToken($credentials)
        ;

        if (!$credential = $this->findAuthCredential($socialUser)) {
            $credential = $this->authCredentialManager->create();
            $this->fillAuthCredential($credential, $socialUser);
        }

        return $credential;
    }

    /**
     * @return OAuth2ClientInterface
     */
    abstract protected function getClient(): OAuth2ClientInterface;

    /**
     * @param ResourceOwnerInterface $socialUser
     *
     * @return AuthCredential|null
     */
    abstract protected function findAuthCredential(ResourceOwnerInterface $socialUser): ?AuthCredential;

    /**
     * @param AuthCredential $credential
     * @param ResourceOwnerInterface $socialUser
     */
    abstract protected function fillAuthCredential(AuthCredential $credential, ResourceOwnerInterface $socialUser): void;
}
