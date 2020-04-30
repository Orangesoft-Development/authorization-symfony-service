<?php

namespace App\Tests\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Entity\SmsCode;
use App\Security\Guard\AbstractAuthenticator;
use App\Security\Guard\PhoneAuthenticator;
use App\Service\SmsCodeManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PhoneAuthenticatorTest extends AbstractLoginAuthenticatorTest
{
    /**
     * @var EncoderFactoryInterface|MockObject
     */
    private $encoderFactory;

    /**
     * @var SmsCodeManager|MockObject
     */
    private $smsCodeManager;

    /**
     * @var PhoneAuthenticator
     */
    protected $authenticator;

    protected function setUp(): void
    {
        $credentials = $this->getCredentials();

        $smsCode = new SmsCode();
        $smsCode
            ->setPhone($credentials['phone'])
            ->setPlainSmsCode($credentials['sms_code'])
            ->setSmsCode('encoded_sms_code')
        ;

        $this->smsCodeManager = $this->getSmsCodeManager($smsCode);
        $this->encoderFactory = $this->getEncoderFactory($smsCode);

        parent::setUp();
    }

    public function testSupports(): void
    {
        $request = new Request([], [
            'method' => AuthMethodType::PHONE,
        ]);

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testGetCredentials(): void
    {
        $credentials = $this->getCredentials();
        $request = new Request([], $credentials);

        $this->assertEquals($credentials, $this->authenticator->getCredentials($request));
    }

    public function testCheckCredentials(): void
    {
        $credentials = $this->getCredentials();

        $this->assertTrue($this->authenticator->checkCredentials($credentials, new Account()));
    }

    public function testGetExistsAuthCredential(): void
    {
        $credentials = $this->getCredentials();
        $authCredential = new AuthCredential();
        $this->createPhoneAuthCredential($credentials, $authCredential);

        $this->assertEquals($authCredential, $this->authenticator->getAuthCredential($credentials));
    }

    public function testGetNotExistsAuthCredential(): void
    {
        $credentials = $this->getCredentials();
        $this->createPhoneAuthCredential($credentials, null);

        $authCredential = new AuthCredential();
        $this->authCredentialManager
            ->expects($this->once())
            ->method('create')
            ->willReturn($authCredential)
        ;

        $this->assertEquals($authCredential, $this->authenticator->getAuthCredential($credentials));
    }

    /**
     * @return PhoneAuthenticator|AbstractAuthenticator
     */
    protected function getAuthenticator(): AbstractAuthenticator
    {
        return new PhoneAuthenticator(
            $this->encoderFactory,
            $this->smsCodeManager,
            $this->accountManager,
            $this->authCredentialManager,
            $this->successHandler,
            $this->failureHandler
        );
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        return [
            'phone' => 'phone',
            'sms_code' => 'sms_code',
        ];
    }

    /**
     * @param array $credentials
     * @param Account|null $account
     */
    protected function createAuthCredential($credentials, ?Account $account): void
    {
        $authCredential = new AuthCredential();

        if (null !== $account) {
            $authCredential->setAccount($account);
        }

        $this->createPhoneAuthCredential($credentials, $authCredential);
    }

    /**
     * @param array $credentials
     * @param AuthCredential|null $authCredential
     */
    private function createPhoneAuthCredential($credentials, ?AuthCredential $authCredential): void
    {
        $this->authCredentialManager
            ->expects($this->any())
            ->method('findCredentialByPhone')
            ->with($credentials['phone'])
            ->willReturn($authCredential)
        ;
    }

    /**
     * @param SmsCode $smsCode
     *
     * @return SmsCodeManager|MockObject
     */
    private function getSmsCodeManager(SmsCode $smsCode): MockObject
    {
        /** @var SmsCodeManager|MockObject $smsCodeManager */
        $smsCodeManager = $this->createMock(SmsCodeManager::class);
        $smsCodeManager
            ->expects($this->any())
            ->method('findSmsCodeByPhone')
            ->with($smsCode->getPhone())
            ->willReturn($smsCode)
        ;

        return $smsCodeManager;
    }

    /**
     * @param SmsCode $smsCode
     *
     * @return EncoderFactoryInterface|MockObject
     */
    private function getEncoderFactory(SmsCode $smsCode): MockObject
    {
        /** @var PasswordEncoderInterface|MockObject $encoder */
        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $encoder
            ->expects($this->any())
            ->method('isPasswordValid')
            ->with($smsCode->getSmsCode(), $smsCode->getPlainSmsCode(), $smsCode->getSalt())
            ->willReturn(true)
        ;

        /** @var EncoderFactoryInterface|MockObject $encoderFactory */
        $encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $encoderFactory
            ->expects($this->any())
            ->method('getEncoder')
            ->with(SmsCode::class)
            ->willReturn($encoder)
        ;

        return $encoderFactory;
    }
}
