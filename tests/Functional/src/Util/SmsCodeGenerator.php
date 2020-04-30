<?php

namespace App\Tests\Functional\src\Util;

use App\Util\SmsCodeGenerator as BaseSmsCodeGenerator;

class SmsCodeGenerator extends BaseSmsCodeGenerator
{
    /**
     * @return string
     */
    public function generateSmsCode(): string
    {
        return 'sms_code';
    }
}
