<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use Jampire\OAuth2\Client\Provider\AppIdResourceOwner;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\AppleClient;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

class AppleIdAuthenticator extends AbstractSocialAuthenticator
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->get('method') === AuthMethodType::APPLE;
    }

    /**
     * @return AppleClient|OAuth2ClientInterface
     */
    protected function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('appid');
    }

    /**
     * @param AppIdResourceOwner|ResourceOwnerInterface $appleUser
     *
     * @return AuthCredential|null
     */
    protected function findAuthCredential(ResourceOwnerInterface $appleUser): ?AuthCredential
    {
        return $this->authCredentialManager
            ->findCredentialByApple($appleUser->getId())
        ;
    }

    /**
     * @param AuthCredential $credential
     * @param AppIdResourceOwner|ResourceOwnerInterface $appleUser
     */
    protected function fillAuthCredential(AuthCredential $credential, ResourceOwnerInterface $appleUser): void
    {
        $credential
            ->setMethod(AuthMethodType::APPLE)
            ->setName($appleUser->getFullName())
            ->setUsername($appleUser->getId())
        ;
    }
}
