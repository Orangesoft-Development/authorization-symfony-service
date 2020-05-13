<?php

namespace App\Tests\Functional\Controller;

use App\DBAL\Types\AuthMethodType;
use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Entity\SmsCode;
use App\Tests\Functional\AbstractWebTestCase;
use App\Tests\Functional\src\Util\SmsCodeGenerator;
use App\Util\SmsCodeGenerator as BaseSmsCodeGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthControllerTest extends AbstractWebTestCase
{
    use AuthCredentialTrait;

    /**
     * @return string
     */
    public function testSendSms(): string
    {
        $phone = 'test_phone';

        self::$client->request(Request::METHOD_POST, '/auth/send-sms', [
            'phone' => $phone,
        ]);

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, self::$client->getResponse()->getStatusCode());

        return $phone;
    }

    /**
     * @param string $phone
     *
     * @return array
     *
     * @depends testSendSms
     */
    public function testLoginByPhone(string $phone): array
    {
        /** @var SmsCodeGenerator $generator */
        $generator = $this->getContainer()->get(BaseSmsCodeGenerator::class);

        $authCredential = $this->getAuthCredential(AuthMethodType::PHONE, $phone, $phone);
        self::$client->request(Request::METHOD_POST, '/auth/login', [
            'method' => $authCredential->getMethod(),
            'phone' => $authCredential->getUsername(),
            'sms_code' => $generator->generateSmsCode(),
        ]);

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertLoginRequiredFields($response, $authCredential);

        return [
            'access_token' => $response['auth_tokens']['access_token'],
            'refresh_token' => $response['auth_tokens']['refresh_token'],
        ];
    }

    /**
     * @param AuthCredential $authCredential
     *
     * @dataProvider socialAuthCredentials
     */
    public function testLoginBySocial(AuthCredential $authCredential): void
    {
        $accessToken = $this->getSocialAccessToken();

        self::$client->request(Request::METHOD_POST, '/auth/login', [
            'method' => $authCredential->getMethod(),
            'access_token' => $accessToken->getToken(),
        ]);

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertLoginRequiredFields($response, $authCredential);
    }

    /**
     * @param array $authTokens
     *
     * @depends testLoginByPhone
     */
    public function testHasExistsRefreshToken(array $authTokens): void
    {
        self::$client->request(Request::METHOD_HEAD, '/auth/refresh-token', [
            'access_token' => $authTokens['access_token'],
        ]);

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, self::$client->getResponse()->getStatusCode());
    }

    public function testHasNotExistsRefreshToken(): void
    {
        self::$client->request(Request::METHOD_HEAD, '/auth/refresh-token', [
            'access_token' => 'not_exists_access_token',
        ]);

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @param array $authTokens
     *
     * @return array
     *
     * @depends testLoginByPhone
     */
    public function testRefreshToken(array $authTokens): array
    {
        self::$client->request(Request::METHOD_POST, '/auth/refresh-token', $authTokens);

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);

        return $response;
    }

    /**
     * @param array $authTokens
     *
     * @depends testRefreshToken
     */
    public function testLogout(array $authTokens): void
    {
        self::$client->request(Request::METHOD_POST, '/auth/logout', [
            'access_token' => $authTokens['access_token'],
        ]);

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, self::$client->getResponse()->getStatusCode());
    }

    public function testLogoutWithException(): void
    {
        self::$client->request(Request::METHOD_POST, '/auth/logout', [
            'access_token' => 'not_exists_access_token'
        ]);

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @param array $response
     * @param AuthCredential $authCredential
     */
    protected function assertLoginRequiredFields(array $response, AuthCredential $authCredential): void
    {
        $this->assertArrayHasKey('auth_tokens', $response);
        $this->assertArrayHasKey('access_token', $response['auth_tokens']);
        $this->assertArrayHasKey('refresh_token', $response['auth_tokens']);

        $this->assertArrayHasKey('account', $response);
        $this->assertArrayHasKey('id', $response['account']);
        $this->assertArrayHasKey('auth_credentials', $response['account']);
        $this->assertGreaterThan(0, count($response['account']['auth_credentials']));

        $this->assertRequiredAuthCredentialFields($authCredential, $response['account']['auth_credentials']);
    }

    /**
     * @return array
     */
    protected function getUsedEntities(): array
    {
        return [
            SmsCode::class,
            AuthCredential::class,
            Account::class,
        ];
    }
}
