<?php

namespace App\Tests\Service\SmsSender;

use App\Entity\SmsCode;
use App\Service\SmsSender\TwilioSmsSender;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twilio\Rest\Api;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class TwilioSmsSenderTest extends KernelTestCase
{
    public function testSendCode(): void
    {
        $smsCode = new SmsCode();
        $smsCode
            ->setPhone('to_phone_number')
            ->setPlainSmsCode('sms_code')
        ;

        $body = 'code ' . $smsCode->getPlainSmsCode();
        $payload = [
            'body' => $body,
            'to' => $smsCode->getPhone(),
        ];
        $options = [
            'body' => $body,
            'from' => 'from_phone_number',
        ];

        /** @var Api\V2010|MockObject $version */
        $version = $this->createMock(Api\V2010::class);
        $messageInstance = new MessageInstance($version, $options + $payload, 'account_sid');

        $messagesMock = $this->createMock(MessageList::class);
        $messagesMock
            ->expects($this->once())
            ->method('create')
            ->with($smsCode->getPhone(), $options)
            ->willReturn($messageInstance)
        ;

        /** @var Client|MockObject $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->expects($this->once())
            ->method('__get')
            ->with('messages')
            ->willReturn($messagesMock)
        ;

        $twilioSmsSender = new TwilioSmsSender($clientMock, $options['from']);
        $twilioSmsSender->sendCode($smsCode);

        $this->assertEquals($messageInstance, $twilioSmsSender->getMessage());
    }
}
