<?php

namespace App\Tests\Functional\src\Service;

use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient as BaseFacebookClient;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Token\AccessToken;

class FacebookClient extends BaseFacebookClient
{
    /**
     * @param AccessToken $accessToken
     *
     * @return FacebookUser
     */
    public function fetchUserFromToken(AccessToken $accessToken): FacebookUser
    {
        return new FacebookUser([
            'id' => 'facebook_id',
            'name' => 'facebook_name',
        ]);
    }
}
