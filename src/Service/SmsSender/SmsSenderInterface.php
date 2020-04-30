<?php

namespace App\Service\SmsSender;

use App\Entity\SmsCode;

interface SmsSenderInterface
{
    /**
     * @param SmsCode $smsCode
     */
    public function sendCode(SmsCode $smsCode): void;

    /**
     * @return array
     */
    public function getResponse(): array;
}
