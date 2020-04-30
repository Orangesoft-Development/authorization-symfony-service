<?php

namespace App\Util;

use App\Entity\SmsCode;
use Exception;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class SmsCodeUpdater
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * PasswordUpdater constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param SmsCode $smsCode
     */
    public function hashSmsCode(SmsCode $smsCode)
    {
        $plainSmsCode = $smsCode->getPlainSmsCode();

        if (0 === strlen($plainSmsCode)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder(SmsCode::class);

        if ($encoder instanceof NativePasswordEncoder) {
            $smsCode->setSalt(null);
        } else {
            try {
                $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
                $smsCode->setSalt($salt);
            } catch (Exception $e) {
                $smsCode->setSalt(null);
            }
        }

        $hashedSmsCode = $encoder->encodePassword($plainSmsCode, $smsCode->getSalt());
        $smsCode->setSmsCode($hashedSmsCode);
        $smsCode->setPlainSmsCode(null);
    }
}
