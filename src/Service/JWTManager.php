<?php

namespace App\Service;

use App\Entity\Account;
use App\Service\Encoder\JWTEncoderInterface;

/**
 * Provides convenient methods to manage JWT creation/verification.
 */
class JWTManager
{
    /**
     * @var JWTEncoderInterface
     */
    protected $jwtEncoder;

    /**
     * JWTManager constructor.
     *
     * @param JWTEncoderInterface $encoder
     */
    public function __construct(JWTEncoderInterface $encoder)
    {
        $this->jwtEncoder = $encoder;
    }

    /**
     * @param Account $account
     *
     * @return string
     */
    public function create(Account $account): string
    {
        return $this->jwtEncoder->encode([
            'account_id' => $account->getId(),
        ]);
    }

    /**
     * @param string $token
     *
     * @return array
     */
    public function decode(string $token): array
    {
        return $this->jwtEncoder->decode($token);
    }
}
