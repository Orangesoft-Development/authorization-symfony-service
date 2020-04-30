<?php

namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class AuthMethodType extends AbstractEnumType
{
    const PHONE = 'phone';
    const APPLE = 'apple';
    const GOOGLE = 'google';
    const FACEBOOK = 'facebook';

    protected static $choices = [
        self::PHONE => self::PHONE,
        self::APPLE => self::APPLE,
        self::GOOGLE => self::GOOGLE,
        self::FACEBOOK => self::FACEBOOK,
    ];
}
