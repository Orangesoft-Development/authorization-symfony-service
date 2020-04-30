<?php

namespace App\Tests\Functional\src\Service;

use App\Entity\SmsCode;
use App\Service\SmsSender\SmsSenderInterface;

class SmsSender implements SmsSenderInterface
{
    /**
     * @var array
     */
    private $response = [];

    /**
     * @param SmsCode $smsCode
     */
    public function sendCode(SmsCode $smsCode): void
    {
        $this->response = [
            'to' => $smsCode->getPhone(),
            'body' => 'code ' . $smsCode->getPlainSmsCode(),
        ];
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }
}
