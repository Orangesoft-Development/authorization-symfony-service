<?php

namespace App\Tests\Functional\src\Service;

use Jampire\OAuth2\Client\Provider\AppIdResourceOwner;
use KnpU\OAuth2ClientBundle\Client\Provider\AppIdClient as BaseAppIdClient;
use League\OAuth2\Client\Token\AccessToken;

class AppIdClient extends BaseAppIdClient
{
    /**
     * @param AccessToken $accessToken
     *
     * @return AppIdResourceOwner
     */
    public function fetchUserFromToken(AccessToken $accessToken): AppIdResourceOwner
    {
        return new AppIdResourceOwner([
            'sub' => 'apple_id',
            'name' => 'apple_name',
        ]);
    }
}
