<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DefaultAuthenticator extends AbstractAuthenticator
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        $method = $request->get('method');

        return !$method || !in_array($method, AuthMethodType::getValues());
    }

    /**
     * @param Request $request
     */
    public function getCredentials(Request $request): void
    {
        throw new BadRequestHttpException('Bad request');
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        return null;
    }
}
