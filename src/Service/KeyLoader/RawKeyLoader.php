<?php

namespace App\Service\KeyLoader;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

class RawKeyLoader extends AbstractKeyLoader
{
    /**
     * @param string $type
     *
     * @return string
     *
     * @throws InvalidArgumentException If the key cannot be read
     */
    public function loadKey(string $type): string
    {
        if (!in_array($type, [self::TYPE_PUBLIC, self::TYPE_PRIVATE])) {
            throw new InvalidArgumentException(
                sprintf('The key type must be "public" or "private", "%s" given.', $type)
            );
        }

        if (self::TYPE_PUBLIC === $type) {
            return $this->dumpKey();
        }

        return $this->getSigningKey();
    }

    /**
     * @return string
     *
     * @throws RuntimeException
     */
    public function dumpKey(): string
    {
        if ($publicKey = $this->getPublicKey()) {
            return $publicKey;
        }

        $signingKey = $this->getSigningKey();

        // no public key provided, compute it from signing key
        try {
            $publicKey = openssl_pkey_get_details(openssl_pkey_get_private($signingKey, $this->getPassphrase()))['key'];
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Secret key either does not exist, is not readable or is invalid. Did you correctly set the config option?'
            );
        }

        return $publicKey;
    }
}
