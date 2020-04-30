<?php

namespace App\Tests\Functional\src\Service;

use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient as BaseGoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;

class GoogleClient extends BaseGoogleClient
{
    /**
     * @param AccessToken $accessToken
     *
     * @return GoogleUser
     */
    public function fetchUserFromToken(AccessToken $accessToken): GoogleUser
    {
        return new GoogleUser([
            'sub' => 'google_id',
            'name' => 'google_name',
        ]);
    }
}
