<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/connect/facebook", name="connect_facebook_start")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return RedirectResponse
     */
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('facebook')
            ->redirect([], [])
        ;
    }

    /**
     * @Rest\Get("/connect/facebook/check", name="connect_facebook_check")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return View
     * @throws IdentityProviderException
     */
    public function connectCheck(ClientRegistry $clientRegistry): View
    {
        /** @var FacebookClient $client */
        $client = $clientRegistry->getClient('facebook');

        $token = $client->getAccessToken();

        /** @var FacebookUser $user */
        $user = $client->fetchUserFromToken($token);

        return View::create([
            'token' => $token,
            'user' => $user,
        ], JsonResponse::HTTP_OK);
    }
}
