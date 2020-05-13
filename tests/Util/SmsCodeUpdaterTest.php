<?php

namespace App\Tests\Util;

use App\Entity\SmsCode;
use App\Util\SmsCodeUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

class SmsCodeUpdaterTest extends TestCase
{
    /**
     * @var EncoderFactoryInterface|MockObject
     */
    private $encodeFactory;

    /**
     * @var SmsCodeUpdater
     */
    private $smsCodeUpdater;

    protected function setUp(): void
    {
        $this->encodeFactory = $this->createMock(EncoderFactoryInterface::class);

        $this->smsCodeUpdater = new SmsCodeUpdater($this->encodeFactory);
    }

    public function testHashSmsCode(): void
    {
        $smsCode = $this->getSmsCode();
        $plainSmsCode = $smsCode->getPlainSmsCode();

        $encoder = new NativePasswordEncoder();
        $this->encodeFactory
            ->expects($this->once())
            ->method('getEncoder')
            ->willReturn($encoder)
        ;

        $this->smsCodeUpdater->hashSmsCode($smsCode);

        $this->assertTrue($encoder->isPasswordValid($smsCode->getSmsCode(), $plainSmsCode, null));
        $this->assertNull($smsCode->getPlainSmsCode());
    }

    /**
     * @return SmsCode
     */
    private function getSmsCode(): SmsCode
    {
        $smsCode = new SmsCode();
        $smsCode->setPlainSmsCode('plain_sms_code');

        return $smsCode;
    }
}
