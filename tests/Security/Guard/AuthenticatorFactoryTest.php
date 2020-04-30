<?php


namespace App\Tests\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Security\Guard\AppleIdAuthenticator;
use App\Security\Guard\AuthenticatorFactory;
use App\Security\Guard\FacebookAuthenticator;
use App\Security\Guard\GoogleAuthenticator;
use App\Security\Guard\PhoneAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticatorFactoryTest extends TestCase
{
    public function testGetPhoneAuthenticator(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::PHONE,
        ]);

        $authenticatorFactory = $this->getAuthenticatorFactory($request);

        $this->assertInstanceOf(PhoneAuthenticator::class, $authenticatorFactory->get($request));
    }

    public function testGetFacebookAuthenticator(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::FACEBOOK,
        ]);

        $authenticatorFactory = $this->getAuthenticatorFactory($request);

        $this->assertInstanceOf(FacebookAuthenticator::class, $authenticatorFactory->get($request));
    }

    public function testGetGoogleAuthenticator(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::GOOGLE,
        ]);

        $authenticatorFactory = $this->getAuthenticatorFactory($request);

        $this->assertInstanceOf(GoogleAuthenticator::class, $authenticatorFactory->get($request));
    }

    public function testGetAppleIdAuthenticator(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::APPLE,
        ]);

        $authenticatorFactory = $this->getAuthenticatorFactory($request);

        $this->assertInstanceOf(AppleIdAuthenticator::class, $authenticatorFactory->get($request));
    }

    public function testGetException(): void
    {
        $request = new Request([], [
            'method' => 'undefined_method',
        ]);

        $authenticatorFactory = $this->getAuthenticatorFactory($request);

        $this->expectException(AuthenticationException::class);
        $authenticatorFactory->get($request);
    }

    /**
     * @param Request $request
     *
     * @return PhoneAuthenticator|MockObject
     */
    private function getPhoneAuthenticator(Request $request): MockObject
    {
        /** @var PhoneAuthenticator|MockObject $phoneAuthenticator */
        $phoneAuthenticator = $this->createMock(PhoneAuthenticator::class);
        $phoneAuthenticator
            ->expects($this->any())
            ->method('supports')
            ->with($request)
            ->willReturn($request->get('method') == AuthMethodType::PHONE)
        ;

        return $phoneAuthenticator;
    }

    /**
     * @param Request $request
     *
     * @return FacebookAuthenticator|MockObject
     */
    private function getFacebookAuthenticator(Request $request): MockObject
    {
        /** @var FacebookAuthenticator|MockObject $facebookAuthenticator */
        $facebookAuthenticator = $this->createMock(FacebookAuthenticator::class);
        $facebookAuthenticator
            ->expects($this->any())
            ->method('supports')
            ->with($request)
            ->willReturn($request->get('method') == AuthMethodType::FACEBOOK)
        ;

        return $facebookAuthenticator;
    }

    /**
     * @param Request $request
     *
     * @return GoogleAuthenticator|MockObject
     */
    private function getGoogleAuthenticator(Request $request): MockObject
    {
        /** @var GoogleAuthenticator|MockObject $googleAuthenticator */
        $googleAuthenticator = $this->createMock(GoogleAuthenticator::class);
        $googleAuthenticator
            ->expects($this->any())
            ->method('supports')
            ->with($request)
            ->willReturn($request->get('method') == AuthMethodType::GOOGLE)
        ;

        return $googleAuthenticator;
    }

    /**
     * @param Request $request
     *
     * @return AppleIdAuthenticator|MockObject
     */
    private function getAppleIdAuthenticator(Request $request): MockObject
    {
        /** @var AppleIdAuthenticator|MockObject $appleIdAuthenticator */
        $appleIdAuthenticator = $this->createMock(AppleIdAuthenticator::class);
        $appleIdAuthenticator
            ->expects($this->any())
            ->method('supports')
            ->with($request)
            ->willReturn($request->get('method') == AuthMethodType::APPLE)
        ;

        return $appleIdAuthenticator;
    }

    /**
     * @param Request $request
     *
     * @return AuthenticatorFactory
     */
    private function getAuthenticatorFactory(Request $request): AuthenticatorFactory
    {
        return new AuthenticatorFactory(
            $this->getPhoneAuthenticator($request),
            $this->getFacebookAuthenticator($request),
            $this->getGoogleAuthenticator($request),
            $this->getAppleIdAuthenticator($request)
        );
    }
}
