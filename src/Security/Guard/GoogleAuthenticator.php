<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

class GoogleAuthenticator extends AbstractSocialAuthenticator
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->get('method') === AuthMethodType::GOOGLE;
    }

    /**
     * @return GoogleClient|OAuth2ClientInterface
     */
    protected function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }

    /**
     * @param GoogleUser|ResourceOwnerInterface $googleUser
     *
     * @return AuthCredential|null
     */
    protected function findAuthCredential(ResourceOwnerInterface $googleUser): ?AuthCredential
    {
        return $this->authCredentialManager
            ->findCredentialByGoogle($googleUser->getId())
        ;
    }

    /**
     * @param AuthCredential $credential
     * @param GoogleUser|ResourceOwnerInterface $googleUser
     */
    protected function fillAuthCredential(AuthCredential $credential, ResourceOwnerInterface $googleUser): void
    {
        $credential
            ->setMethod(AuthMethodType::GOOGLE)
            ->setName($googleUser->getName())
            ->setUsername($googleUser->getId())
        ;
    }
}
