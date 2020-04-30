<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/connect/google", name="connect_google_start")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return RedirectResponse
     */
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([], [])
        ;
    }

    /**
     * @Rest\Get("/connect/google/check", name="connect_google_check")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return View
     * @throws IdentityProviderException
     */
    public function connectCheck(ClientRegistry $clientRegistry): View
    {
        /** @var GoogleClient $client */
        $client = $clientRegistry->getClient('google');

        $token = $client->getAccessToken();

        /** @var GoogleUser $user */
        $user = $client->fetchUserFromToken($token);

        return View::create([
            'token' => $token,
            'user' => $user,
        ], JsonResponse::HTTP_OK);
    }
}
