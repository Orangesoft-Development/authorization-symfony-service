<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

class FacebookAuthenticator extends AbstractSocialAuthenticator
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->get('method') === AuthMethodType::FACEBOOK;
    }

    /**
     * @return FacebookClient|OAuth2ClientInterface
     */
    protected function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('facebook');
    }

    /**
     * @param FacebookUser|ResourceOwnerInterface $facebookUser
     *
     * @return AuthCredential|null
     */
    protected function findAuthCredential(ResourceOwnerInterface $facebookUser): ?AuthCredential
    {
        return $this->authCredentialManager
            ->findCredentialByFacebook($facebookUser->getId())
        ;
    }

    /**
     * @param AuthCredential $credential
     * @param FacebookUser|ResourceOwnerInterface $facebookUser
     */
    protected function fillAuthCredential(AuthCredential $credential, ResourceOwnerInterface $facebookUser): void
    {
        $credential
            ->setMethod(AuthMethodType::FACEBOOK)
            ->setName($facebookUser->getName())
            ->setUsername($facebookUser->getId())
        ;
    }
}
