<?php

namespace App\Tests\Service\Encoder;

use App\Service\Encoder\LcobucciJWTEncoder;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LcobucciJWTEncoderTest extends KernelTestCase
{
    /**
     * @var array
     */
    private static $payload = [
        'account_id' => 1,
    ];

    /**
     * @var LcobucciJWTEncoder
     */
    private $lcobucciJWTEncoder;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->lcobucciJWTEncoder = self::$container->get(LcobucciJWTEncoder::class);
    }

    /**
     * @return string
     */
    public function testEncode(): string
    {
        $token = $this->lcobucciJWTEncoder->encode(self::$payload);

        $this->assertIsString($token);

        return $token;
    }

    /**
     * @param string $token
     *
     * @depends testEncode
     *
     * @throws Exception
     */
    public function testDecode(string $token): void
    {
        $payload = $this->lcobucciJWTEncoder->decode($token);

        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertArrayHasKey('account_id', $payload);

        $this->assertEquals(self::$payload['account_id'], $payload['account_id']);
    }
}
