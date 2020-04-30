<?php

namespace App\Service\KeyLoader;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractKeyLoader
{
    const TYPE_PUBLIC = 'public';

    const TYPE_PRIVATE = 'private';

    /**
     * @var string
     */
    private $signingKey;

    /**
     * @var string|null
     */
    private $publicKey;

    /**
     * @var string|null
     */
    private $passphrase;

    /**
     * AbstractKeyLoader constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $config = $parameterBag->get('jwt_authentication');

        $this->signingKey = $config['secret_key'];
        $this->publicKey = $config['public_key'];
        $this->passphrase = $config['pass_phrase'];
    }

    /**
     * @return string|null
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * @return string|null
     */
    protected function getSigningKey()
    {
        return is_file($this->signingKey) ? $this->readKey(self::TYPE_PRIVATE) : $this->signingKey;
    }

    /**
     * @return string|null
     */
    protected function getPublicKey()
    {
        return is_file($this->publicKey) ? $this->readKey(self::TYPE_PUBLIC) : $this->publicKey;
    }

    /**
     * @param string $type One of "public" or "private"
     *
     * @return string The path of the key, an empty string if not a valid path
     *
     * @throws InvalidArgumentException If the given type is not valid
     * @throws RuntimeException If key not found or not readable
     */
    protected function getKeyPath(string $type): string
    {
        if (!in_array($type, [self::TYPE_PUBLIC, self::TYPE_PRIVATE])) {
            throw new InvalidArgumentException(
                sprintf('The key type must be "public" or "private", "%s" given.', $type)
            );
        }

        $path = self::TYPE_PUBLIC === $type ? $this->publicKey : $this->signingKey;

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(
                sprintf('%s key is not a file or is not readable.', ucfirst($type))
            );
        }

        return $path;
    }

    /**
     * @param string $type
     *
     * @return string|null
     *
     * @throws RuntimeException If key not found or not readable
     */
    private function readKey(string $type): ?string
    {
        $isPublic = self::TYPE_PUBLIC === $type;
        $key = $isPublic ? $this->publicKey : $this->signingKey;

        if (!$key || !is_file($key) || !is_readable($key)) {
            if ($isPublic) {
                return null;
            }

            throw new RuntimeException(
                sprintf('Signature key "%s" does not exist or is not readable. Did you correctly set the configuration key?', $key)
            );
        }

        if ($keyContents = file_get_contents($key)) {
            return $keyContents;
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    abstract public function loadKey(string $type): string;
}
