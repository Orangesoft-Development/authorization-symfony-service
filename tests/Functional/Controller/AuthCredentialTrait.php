<?php

namespace App\Tests\Functional\Controller;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Tests\Functional\src\Service\AppIdClient;
use App\Tests\Functional\src\Service\FacebookClient;
use App\Tests\Functional\src\Service\GoogleClient;
use League\OAuth2\Client\Token\AccessToken;

trait AuthCredentialTrait
{
    public function socialAuthCredentials(): array
    {
        return [
            'GoogleAuthCredential' => [$this->getGoogleAuthCredential()],
            'FacebookAuthCredential' => [$this->getFacebookAuthCredential()],
            'AppleIdAuthCredential' => [$this->getAppleIdAuthCredential()],
        ];
    }

    /**
     * @param AuthCredential $authCredential
     * @param array $responseAuthCredentials
     */
    protected function assertRequiredAuthCredentialFields(
        AuthCredential $authCredential,
        array $responseAuthCredentials
    ): void {
        $responseAuthCredential = $this->getResponseAuthCredential(
            $responseAuthCredentials,
            $authCredential->getMethod()
        );

        $this->assertNotNull($responseAuthCredential);

        $this->assertArrayHasKey('method', $responseAuthCredential);
        $this->assertArrayHasKey('name', $responseAuthCredential);
        $this->assertSame($authCredential->getMethod(), $responseAuthCredential['method']);
        $this->assertSame($authCredential->getName(), $responseAuthCredential['name']);
    }

    /**
     * @param string $method
     * @param string|null $name
     * @param string|null $username
     *
     * @return AuthCredential
     */
    private function getAuthCredential(
        string $method,
        ?string $name = null,
        ?string $username = null
    ): AuthCredential {
        $authCredential = new AuthCredential();
        $authCredential
            ->setMethod($method)
            ->setName($name ?? $method . '_name')
            ->setUsername($username ?? $method . '_username')
        ;

        return $authCredential;
    }

    /**
     * @return AccessToken
     */
    protected function getSocialAccessToken(): AccessToken
    {
        return new AccessToken([
            'access_token' => 'access_token',
        ]);
    }

    /**
     * @return AuthCredential
     */
    protected function getGoogleAuthCredential(): AuthCredential
    {
        $accessToken = $this->getSocialAccessToken();

        /** @var GoogleClient $googleClient */
        $googleClient = $this->getContainer()->get('knpu.oauth2.client.google');
        $googleUser = $googleClient->fetchUserFromToken($accessToken);

        return $this->getAuthCredential(
            AuthMethodType::GOOGLE,
            $googleUser->getName(),
            $googleUser->getId()
        );
    }

    /**
     * @return AuthCredential
     */
    protected function getFacebookAuthCredential(): AuthCredential
    {
        $accessToken = $this->getSocialAccessToken();

        /** @var FacebookClient $facebookClient */
        $facebookClient = $this->getContainer()->get('knpu.oauth2.client.facebook');
        $facebookUser = $facebookClient->fetchUserFromToken($accessToken);

        return $this->getAuthCredential(
            AuthMethodType::FACEBOOK,
            $facebookUser->getName(),
            $facebookUser->getId()
        );
    }

    /**
     * @return AuthCredential
     */
    protected function getAppleIdAuthCredential(): AuthCredential
    {
        $accessToken = $this->getSocialAccessToken();

        /** @var AppIdClient $appIdClient */
        $appIdClient = $this->getContainer()->get('knpu.oauth2.client.appid');
        $appIdUser = $appIdClient->fetchUserFromToken($accessToken);

        return $this->getAuthCredential(
            AuthMethodType::APPLE,
            $appIdUser->getFullName(),
            $appIdUser->getId()
        );
    }

    /**
     * @param array $authCredentials
     * @param string $method
     *
     * @return array|null
     */
    protected function getResponseAuthCredential(array $authCredentials, string $method): ?array
    {
        foreach ($authCredentials as $authCredential) {
            if (isset($authCredential['method']) && $authCredential['method'] == $method) {
                return $authCredential;
            }
        }

        return null;
    }
}
