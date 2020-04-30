<?php

namespace App\Service\Encoder;

use App\Service\KeyLoader\RawKeyLoader;
use DateTime;
use Exception;
use InvalidArgumentException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Json Web Token encoder/decoder based on the lcobucci/jwt library.
 */
class LcobucciJWTEncoder implements JWTEncoderInterface
{
    /**
     * @var RawKeyLoader
     */
    private $keyLoader;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $clockSkew;

    /**
     * LcobucciJWTEncoder constructor.
     *
     * @param RawKeyLoader $keyLoader
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(RawKeyLoader $keyLoader, ParameterBagInterface $parameterBag)
    {
        $config = $parameterBag->get('jwt_authentication');

        $this->keyLoader = $keyLoader;
        $this->signer = $this->getSignerForAlgorithm($config['signature_algorithm']);
        $this->ttl = $config['token_ttl'];
        $this->clockSkew = $config['clock_skew'];
    }

    /**
     * @param array $payload
     *
     * @return string
     */
    public function encode(array $payload): string
    {
        $jwsBuilder = new Builder();
        $jwsBuilder->issuedAt(time());
        $jwsBuilder->expiresAt(time() + $this->ttl);

        foreach ($payload as $name => $value) {
            $jwsBuilder->withClaim($name, $value);
        }

        return (string) $this->sign($jwsBuilder);
    }

    /**
     * @param string $token
     *
     * @return array
     *
     * @throws Exception
     */
    public function decode(string $token): array
    {
        $jws = (new Parser())->parse($token);
        /** @var Claim[] $claims */
        $claims = $jws->getClaims();

        $payload = [];
        foreach ($claims as $claim) {
            $payload[$claim->getName()] = $claim->getValue();
        }

        if (!$this->verify($jws)) {
            throw new Exception('Unable to verify the given JWT through the given configuration. If the encryption options have been changed since your last authentication, please renew the token. If the problem persists, verify that the configured keys/passphrase are valid.');
        }

        $this->checkExpiration($payload);
        $this->checkIssuedAt($payload);

        return $payload;
    }

    /**
     * @param string $signatureAlgorithm
     *
     * @return Signer
     */
    private function getSignerForAlgorithm(string $signatureAlgorithm): Signer
    {
        $signerMap = [
            'HS256' => Signer\Hmac\Sha256::class,
            'HS384' => Signer\Hmac\Sha384::class,
            'HS512' => Signer\Hmac\Sha512::class,
            'RS256' => Signer\Rsa\Sha256::class,
            'RS384' => Signer\Rsa\Sha384::class,
            'RS512' => Signer\Rsa\Sha512::class,
            'EC256' => Signer\Ecdsa\Sha256::class,
            'EC384' => Signer\Ecdsa\Sha384::class,
            'EC512' => Signer\Ecdsa\Sha512::class,
        ];

        if (!isset($signerMap[$signatureAlgorithm])) {
            throw new InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, __CLASS__)
            );
        }

        $signerClass = $signerMap[$signatureAlgorithm];

        return new $signerClass();
    }

    /**
     * @param Builder $jws
     *
     * @return Token
     */
    private function sign(Builder $jws): Token
    {
        $key = $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE);

        $key = $this->signer instanceof Signer\Hmac ?
            new Key($key) : new Key($key, $this->keyLoader->getPassphrase());

        return $jws->getToken($this->signer, $key);
    }


    private function verify(Token $jwt): bool
    {
        $validationData = new ValidationData(time() + $this->clockSkew);
        if (!$jwt->validate($validationData)) {
            return false;
        }

        if ($this->signer instanceof Signer\Hmac) {
            $key = $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE);

            return $jwt->verify($this->signer, $key);
        }

        $key = $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC);

        return $jwt->verify($this->signer, $key);
    }

    /**
     * Ensures that the signature is not expired.
     *
     * @param array $payload
     *
     * @throws Exception
     */
    private function checkExpiration(array $payload): void
    {
        if (!isset($payload['exp']) || !is_numeric($payload['exp'])) {
            throw new Exception('Invalid JWT Token');
        }

        if ($this->clockSkew <= (new DateTime())->format('U') - $payload['exp']) {
            throw new Exception('Expired JWT Token');
        }
    }

    /**
     * Ensures that the iat claim is not in the future.
     *
     * @param array $payload
     *
     * @throws Exception
     */
    private function checkIssuedAt(array $payload): void
    {
        if (isset($this->payload['iat']) && (int) $payload['iat'] - $this->clockSkew > time()) {
            throw new Exception('Invalid JWT Token');
        }
    }
}
