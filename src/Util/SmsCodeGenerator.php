<?php

namespace App\Util;

use Exception;

class SmsCodeGenerator
{
    /**
     * @return string
     *
     * @throws Exception
     */
    public function generateSmsCode(): string
    {
        return rand(100000, 999999);
    }
}
