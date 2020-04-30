<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Jampire\OAuth2\Client\Provider\AppIdResourceOwner;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\AppleClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AppleIdController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/connect/appid", name="connect_appid_start")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return RedirectResponse
     */
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('appid')
            ->redirect([], [])
        ;
    }

    /**
     * @Rest\Get("/connect/appid/check", name="connect_appid_check")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return View
     * @throws IdentityProviderException
     */
    public function connectCheck(ClientRegistry $clientRegistry): View
    {
        /** @var AppleClient $client */
        $client = $clientRegistry->getClient('appid');

        $token = $client->getAccessToken();

        /** @var AppIdResourceOwner $user */
        $user = $client->fetchUserFromToken($token);

        return View::create([
            'token' => $token,
            'user' => $user,
        ], JsonResponse::HTTP_OK);
    }
}
