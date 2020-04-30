<?php

namespace App\Tests\Service\KeyLoader;

use App\Service\KeyLoader\AbstractKeyLoader;
use App\Service\KeyLoader\RawKeyLoader;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class RawKeyLoaderTest extends TestCase
{
    /**
     * @var RawKeyLoader
     */
    private $keyLoader;

    protected function setUp(): void
    {
        $parameterBag = new ParameterBag([
            'jwt_authentication' => [
                'secret_key' => 'JWT_SECRET_KEY',
                'public_key' => 'JWT_PUBLIC_KEY',
                'pass_phrase' => 'JWT_PASSPHRASE',
            ],
        ]);
        $this->keyLoader = new RawKeyLoader($parameterBag);
    }

    public function testLoadPublicKey(): void
    {
        $publicKey = $this->keyLoader->loadKey(AbstractKeyLoader::TYPE_PUBLIC);

        $this->assertSame('JWT_PUBLIC_KEY', $publicKey);
    }

    public function testLoadPrivateKey(): void
    {
        $privateKey = $this->keyLoader->loadKey(AbstractKeyLoader::TYPE_PRIVATE);

        $this->assertSame('JWT_SECRET_KEY', $privateKey);
    }

    public function testDumpKey(): void
    {
        $dumpKey = $this->keyLoader->dumpKey();

        $this->assertSame('JWT_PUBLIC_KEY', $dumpKey);
    }
}
