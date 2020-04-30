<?php

namespace App\Service\Encoder;

interface JWTEncoderInterface
{
    /**
     * @param array $data
     *
     * @return string the encoded token string
     */
    public function encode(array $data): string;

    /**
     * @param string $token
     *
     * @return array
     */
    public function decode(string $token): array;
}
