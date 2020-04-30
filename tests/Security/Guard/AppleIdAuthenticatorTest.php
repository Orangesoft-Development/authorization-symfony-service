<?php

namespace App\Tests\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\AppleIdAuthenticator;
use Jampire\OAuth2\Client\Provider\AppIdResourceOwner;
use KnpU\OAuth2ClientBundle\Client\Provider\AppleClient;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class AppleIdAuthenticatorTest extends AbstractSocialAuthenticatorTest
{
    /**
     * @var AppleIdAuthenticator
     */
    protected $authenticator;

    public function testSupports(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::APPLE,
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    /**
     * @return AppleClient|MockObject
     */
    protected function getClient(): MockObject
    {
        /** @var AppleClient|MockObject $appleClient */
        $appleClient = $this->createMock(AppleClient::class);

        $this->clientRegistry
            ->expects($this->any())
            ->method('getClient')
            ->with('appid')
            ->willReturn($appleClient)
        ;

        return $appleClient;
    }

    /**
     * @return AppIdResourceOwner|MockObject
     */
    protected function getSocialUser(): MockObject
    {
        /** @var AppIdResourceOwner|MockObject $appleUser */
        $appleUser = $this->createMock(AppIdResourceOwner::class);
        $appleUser
            ->expects($this->any())
            ->method('getId')
            ->willReturn('apple_id')
        ;
        $appleUser
            ->expects($this->any())
            ->method('getFullName')
            ->willReturn('apple_full_name')
        ;

        return $appleUser;
    }

    /**
     * @return AppleIdAuthenticator|AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new AppleIdAuthenticator(
            $this->clientRegistry,
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }

    /**
     * @param AppIdResourceOwner|MockObject $appleUser
     * @param AuthCredential|null $authCredential
     */
    protected function createSocialAuthCredential(MockObject $appleUser, ?AuthCredential $authCredential): void
    {
        $this->authCredentialManager
            ->expects($this->any())
            ->method('findCredentialByApple')
            ->with($appleUser->getId())
            ->willReturn($authCredential)
        ;
    }
}
