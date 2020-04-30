<?php

namespace App\Tests\Security\Guard;

use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Security\Guard\AbstractSocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSocialAuthenticatorTest extends AbstractLoginAuthenticatorTest
{
    /**
     * @var ClientRegistry|MockObject
     */
    protected $clientRegistry;

    /**
     * @var AbstractSocialAuthenticator
     */
    protected $authenticator;

    protected function setUp(): void
    {
        $this->clientRegistry = $this->createMock(ClientRegistry::class);

        parent::setUp();
    }

    public function testGetCredentials(): void
    {
        $accessToken = 'access_token';
        $request = new Request([], [
            'access_token' => $accessToken,
        ]);

        $credentials = $this->authenticator->getCredentials($request);

        $this->assertInstanceOf(AccessToken::class, $credentials);
        $this->assertSame($accessToken, $credentials->getToken());
    }

    public function testGetExistsAuthCredential(): void
    {
        $credentials = $this->getCredentials();
        $socialUser = $this->getResourceOwner($credentials);
        $authCredential = new AuthCredential();
        $this->createSocialAuthCredential($socialUser, $authCredential);

        $this->assertEquals($authCredential, $this->authenticator->getAuthCredential($credentials));
    }

    public function testGetNotExistsAuthCredential(): void
    {
        $credentials = $this->getCredentials();
        $socialUser = $this->getResourceOwner($credentials);
        $this->createSocialAuthCredential($socialUser, null);

        $authCredential = new AuthCredential();
        $this->authCredentialManager
            ->expects($this->once())
            ->method('create')
            ->willReturn($authCredential)
        ;

        $this->assertEquals($authCredential, $this->authenticator->getAuthCredential($credentials));
    }

    /**
     * @return AccessToken
     */
    protected function getCredentials()
    {
        return new AccessToken([
            'access_token' => 'access_token',
        ]);
    }

    /**
     * @param AccessToken $credentials
     *
     * @return ResourceOwnerInterface|MockObject
     */
    protected function getResourceOwner(AccessToken $credentials): MockObject
    {
        $socialUser = $this->getSocialUser();

        $client = $this->getClient();
        $client
            ->expects($this->any())
            ->method('fetchUserFromToken')
            ->with($credentials)
            ->willReturn($socialUser)
        ;

        return $socialUser;
    }

    /**
     * @param AccessToken $credentials
     * @param Account|null $account
     */
    protected function createAuthCredential($credentials, ?Account $account)
    {
        $socialUser = $this->getResourceOwner($credentials);

        $authCredential = new AuthCredential();

        if (null !== $account) {
            $authCredential->setAccount($account);
        }

        $this->createSocialAuthCredential($socialUser, $authCredential);
    }

    /**
     * @return OAuth2ClientInterface|MockObject
     */
    abstract protected function getClient(): MockObject;

    /**
     * @return ResourceOwnerInterface|MockObject
     */
    abstract protected function getSocialUser(): MockObject;

    /**
     * @param ResourceOwnerInterface|MockObject $socialUser
     * @param AuthCredential|null $authCredential
     */
    abstract protected function createSocialAuthCredential(
        MockObject $socialUser,
        ?AuthCredential $authCredential
    ): void;
}
