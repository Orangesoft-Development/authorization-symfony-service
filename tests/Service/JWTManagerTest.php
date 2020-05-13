<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Service\Encoder\JWTEncoderInterface;
use App\Service\JWTManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JWTManagerTest extends TestCase
{
    /**
     * @var JWTEncoderInterface|MockObject
     */
    private $jwtEncoder;

    /**
     * @var JWTManager
     */
    private $jwtManager;

    protected function setUp(): void
    {
        $this->jwtEncoder = $this->createMock(JWTEncoderInterface::class);
        $this->jwtManager = new JWTManager($this->jwtEncoder);
    }

    public function testCreate(): void
    {
        $accountId = 1;
        $token = 'token';

        /** @var Account|MockObject $account */
        $account = $this->createMock(Account::class);
        $account
            ->expects($this->once())
            ->method('getId')
            ->willReturn($accountId)
        ;

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with([
                'account_id' => $accountId,
            ])
            ->willReturn($token)
        ;


        $this->assertSame($token, $this->jwtManager->create($account));
    }

    public function testDecode(): void
    {
        $accountId = 1;
        $token = 'token';
        $payload = [
            'account_id' => $accountId,
        ];

        $this->jwtEncoder
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn($payload)
        ;

        $this->assertEquals($payload, $this->jwtManager->decode($token));
    }
}
