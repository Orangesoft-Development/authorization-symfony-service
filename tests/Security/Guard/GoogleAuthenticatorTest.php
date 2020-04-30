<?php

namespace App\Tests\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\GoogleAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class GoogleAuthenticatorTest extends AbstractSocialAuthenticatorTest
{
    /**
     * @var GoogleAuthenticator
     */
    protected $authenticator;

    public function testSupports(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::GOOGLE,
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    /**
     * @return GoogleAuthenticator|AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new GoogleAuthenticator(
            $this->clientRegistry,
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }

    /**
     * @return GoogleClient|MockObject
     */
    protected function getClient(): MockObject
    {
        /** @var GoogleClient|MockObject $googleClient */
        $googleClient = $this->createMock(GoogleClient::class);

        $this->clientRegistry
            ->expects($this->any())
            ->method('getClient')
            ->with('google')
            ->willReturn($googleClient)
        ;

        return $googleClient;
    }

    /**
     * @return GoogleUser|MockObject
     */
    protected function getSocialUser(): MockObject
    {
        /** @var GoogleUser|MockObject $googleUser */
        $googleUser = $this->createMock(GoogleUser::class);
        $googleUser
            ->expects($this->any())
            ->method('getId')
            ->willReturn('google_id')
        ;
        $googleUser
            ->expects($this->any())
            ->method('getName')
            ->willReturn('google_name')
        ;

        return $googleUser;
    }

    /**
     * @param GoogleUser|MockObject $googleUser
     * @param AuthCredential|null $authCredential
     */
    protected function createSocialAuthCredential(MockObject $googleUser, ?AuthCredential $authCredential): void
    {
        $this->authCredentialManager
            ->expects($this->any())
            ->method('findCredentialByGoogle')
            ->with($googleUser->getId())
            ->willReturn($authCredential)
        ;
    }
}
