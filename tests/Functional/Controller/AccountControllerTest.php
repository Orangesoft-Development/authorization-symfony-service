<?php

namespace App\Tests\Functional\Controller;

use App\DBAL\Types\AuthMethodType;
use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Entity\Session;
use App\Entity\SmsCode;
use App\Tests\Functional\AbstractWebTestCase;
use App\Util\SmsCodeUpdater;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AccountControllerTest extends AbstractWebTestCase
{
    use AuthCredentialTrait;

    /**
     * @return int
     */
    public function testShow(): int
    {
        $defaultAuthCredential = $this->getAuthCredential(AuthMethodType::PHONE);
        $this->getEntityManager()->persist($defaultAuthCredential);

        $account = $this->getAccount();
        $account->addAuthCredential($defaultAuthCredential);

        $this->getEntityManager()->flush();

        self::$client->request(Request::METHOD_GET, '/accounts/' . $account->getId());

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($account->getId(), $response);
        $this->assertRequiredAuthCredentialFields($defaultAuthCredential, $response['auth_credentials']);

        return $account->getId();
    }

    /**
     * @param int $accountId
     *
     * @depends testShow
     */
    public function testEdit(int $accountId): void
    {
        $name = 'new_name';
        $countryId = 'RU'; // RU - Russia ID
        $cityId = 524894; // 524894 - Moscow ID

        self::$client->request(Request::METHOD_PATCH, '/accounts/' . $accountId, [
            'name'       => $name,
            'country_id' => $countryId,
            'city_id'    => $cityId,
        ]);

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);

        $this->assertArrayHasKey('name', $response);

        $this->assertArrayHasKey('country', $response);
        $this->assertArrayHasKey('id', $response['country']);
        $this->assertSame($countryId, $response['country']['id']);

        $this->assertArrayHasKey('city', $response);
        $this->assertArrayHasKey('id', $response['city']);
        $this->assertSame($cityId, $response['city']['id']);
    }

    /**
     * @param int $accountId
     *
     * @depends testShow
     * @depends testEdit
     */
    public function testEditCountry(int $accountId): void
    {
        $countryId = 'BY'; // BY - Belarus ID

        self::$client->request(Request::METHOD_PATCH, '/accounts/' . $accountId, [
            'country_id' => $countryId,
        ]);

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);

        $this->assertArrayHasKey('country', $response);
        $this->assertArrayHasKey('id', $response['country']);
        $this->assertSame($countryId, $response['country']['id']);
        $this->assertArrayNotHasKey('city', $response);
    }

    /**
     * @param int $accountId
     *
     * @return Account
     *
     * @depends testShow
     */
    public function testCreateAvatar(int $accountId): Account
    {
        self::$client->request(
            Request::METHOD_POST,
            '/accounts/' . $accountId . '/avatar',
            [],
            [],
            [],
            file_get_contents(__DIR__ . '/../src/Resource/test-avatar.png')
        );

        $this->assertSame(JsonResponse::HTTP_CREATED, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);

        $this->assertArrayHasKey('avatar_url', $response);
        $this->assertIsString($response['avatar_url']);
        $this->assertFalse(empty($response['avatar_url']));

        $this->getEntityManager()->clear();

        /** @var Account $account */
        $account = $this->getEntityManager()->find(Account::class, $accountId);

        return $account;
    }

    /**
     * @param $account
     *
     * @depends testCreateAvatar
     */
    public function testDeleteAvatar(Account $account): void
    {
        self::$client->request(Request::METHOD_DELETE, '/accounts/' . $account->getId() . '/avatar');

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($account->getId(), $response);

        $this->assertArrayNotHasKey('avatar_url', $response);

        $this->getEntityManager()->clear();

        $account = $this->getEntityManager()->find(Account::class, $account->getId());

        $this->assertNull($account->getAvatarUrl());
    }

    /**
     * @param int $accountId
     *
     * @depends testShow
     */
    public function testCreateAuthCredentialByPhone(int $accountId): void
    {
        $authCredential = $this->getAuthCredential(
            AuthMethodType::PHONE,
            'new_phone',
            'new_phone'
        );

        $strSmsCode = 'new_sms_code';

        /** @var SmsCodeUpdater $smsCodeUpdater */
        $smsCodeUpdater = $this->getContainer()->get(SmsCodeUpdater::class);
        $smsCode = new SmsCode();
        $smsCode
            ->setPhone($authCredential->getUsername())
            ->setPlainSmsCode($strSmsCode)
        ;
        $smsCodeUpdater->hashSmsCode($smsCode);

        $this->getEntityManager()->persist($smsCode);
        $this->getEntityManager()->flush();

        self::$client->request(
            Request::METHOD_POST,
            '/accounts/' . $accountId . '/auth-credentials',
            [
                'method' => $authCredential->getMethod(),
                'phone' => $authCredential->getUsername(),
                'sms_code' => $strSmsCode,
            ]
        );

        $this->assertSame(JsonResponse::HTTP_CREATED, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);
        $this->assertRequiredAuthCredentialFields($authCredential, $response['auth_credentials']);
    }

    /**
     * @param AuthCredential $authCredential
     * @param int $accountId
     *
     * @dataProvider socialAuthCredentials
     * @depends testShow
     */
    public function testCreateAuthCredentialBySocial(AuthCredential $authCredential, int $accountId): void
    {
        $accessToken = $this->getSocialAccessToken();

        self::$client->request(
            Request::METHOD_POST,
            '/accounts/' . $accountId . '/auth-credentials',
            [
                'method' => $authCredential->getMethod(),
                'access_token' => $accessToken->getToken(),
            ]
        );

        $this->assertSame(JsonResponse::HTTP_CREATED, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);
        $this->assertRequiredAuthCredentialFields($authCredential, $response['auth_credentials']);
    }

    /**
     * @param int $accountId
     *
     * @throws DBALException
     *
     * @depends testShow
     */
    public function testDeleteSingleAuthCredential(int $accountId): void
    {
        $this->getEntityManager()->clear();
        $this->truncateEntities([AuthCredential::class]);

        $authCredential = $this->getAuthCredential(AuthMethodType::PHONE);
        $this->getEntityManager()->persist($authCredential);

        /** @var Account $account */
        $account = $this->getEntityManager()->find(Account::class, $accountId);
        $account->addAuthCredential($authCredential);
        $this->getEntityManager()->flush();

        self::$client->request(
            Request::METHOD_DELETE,
            '/accounts/' . $accountId . '/auth-credentials/' . $authCredential->getMethod(),
        );

        $this->assertSame(JsonResponse::HTTP_FORBIDDEN, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @param AuthCredential $socialAuthCredential
     * @param int $accountId
     *
     * @throws DBALException
     *
     * @dataProvider socialAuthCredentials
     * @depends testShow
     */
    public function testDeleteAuthCredential(AuthCredential $socialAuthCredential, int $accountId): void
    {
        $this->getEntityManager()->clear();
        $this->truncateEntities([AuthCredential::class]);

        $this->getEntityManager()->persist($socialAuthCredential);

        $phoneAuthCredential = $this->getAuthCredential(AuthMethodType::PHONE);
        $this->getEntityManager()->persist($phoneAuthCredential);

        /** @var Account $account */
        $account = $this->getEntityManager()->find(Account::class, $accountId);
        $account
            ->addAuthCredential($phoneAuthCredential)
            ->addAuthCredential($socialAuthCredential)
        ;
        $this->getEntityManager()->flush();

        self::$client->request(
            Request::METHOD_DELETE,
            '/accounts/' . $accountId . '/auth-credentials/' . $socialAuthCredential->getMethod(),
        );

        $this->assertSame(JsonResponse::HTTP_OK, self::$client->getResponse()->getStatusCode());

        $response = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertRequiredAccountFields($accountId, $response);
        $this->assertRequiredAuthCredentialFields($phoneAuthCredential, $response['auth_credentials']);
    }

    public function testDelete(): void
    {
        $account = $this->getAccount();
        $this->getEntityManager()->flush();

        self::$client->request(Request::METHOD_DELETE, '/accounts/' . $account->getId());

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, self::$client->getResponse()->getStatusCode());

        $this->getEntityManager()->clear();

        /** @var Account $account */
        $account = $this->getEntityManager()->find(Account::class, $account->getId());
        $this->assertNull($account);
    }

    /**
     * @param int $accountId
     * @param array $response
     */
    protected function assertRequiredAccountFields(int $accountId, array $response): void
    {
        $this->assertArrayHasKey('id', $response);
        $this->assertSame($accountId, $response['id']);

        $this->assertArrayHasKey('auth_credentials', $response);
        $this->assertGreaterThan(0, count($response['auth_credentials']));
    }

    /**
     * @return Account
     */
    private function getAccount(): Account
    {
        $account = new Account();
        $account->setEnabled(true);

        $this->getEntityManager()->persist($account);

        return $account;
    }

    /**
     * @return array
     */
    protected function getUsedEntities(): array
    {
        return [
            SmsCode::class,
            Session::class,
            AuthCredential::class,
            Account::class,
        ];
    }
}
