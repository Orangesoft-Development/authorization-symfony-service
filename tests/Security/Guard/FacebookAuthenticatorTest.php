<?php

namespace App\Tests\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\FacebookAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\FacebookUser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class FacebookAuthenticatorTest extends AbstractSocialAuthenticatorTest
{
    /**
     * @var FacebookAuthenticator
     */
    protected $authenticator;

    public function testSupports(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::FACEBOOK,
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    /**
     * @return FacebookAuthenticator|AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new FacebookAuthenticator(
            $this->clientRegistry,
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }

    /**
     * @return FacebookClient|MockObject
     */
    protected function getClient(): MockObject
    {
        /** @var FacebookClient|MockObject $facebookClient */
        $facebookClient = $this->createMock(FacebookClient::class);

        $this->clientRegistry
            ->expects($this->any())
            ->method('getClient')
            ->with('facebook')
            ->willReturn($facebookClient)
        ;

        return $facebookClient;
    }

    /**
     * @return FacebookUser|MockObject
     */
    protected function getSocialUser(): MockObject
    {
        /** @var FacebookUser|MockObject $facebookUser */
        $facebookUser = $this->createMock(FacebookUser::class);
        $facebookUser
            ->expects($this->any())
            ->method('getId')
            ->willReturn('facebook_id')
        ;
        $facebookUser
            ->expects($this->any())
            ->method('getName')
            ->willReturn('facebook_name')
        ;

        return $facebookUser;
    }

    /**
     * @param FacebookUser|MockObject $facebookUser
     * @param AuthCredential|null $authCredential
     */
    protected function createSocialAuthCredential(MockObject $facebookUser, ?AuthCredential $authCredential): void
    {
        $this->authCredentialManager
            ->expects($this->any())
            ->method('findCredentialByFacebook')
            ->with($facebookUser->getId())
            ->willReturn($authCredential)
        ;
    }
}
