<?php

namespace App\Security\Guard;

use App\Entity\AuthCredential;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class AbstractLoginAuthenticator extends AbstractAuthenticator
{
    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $authCredential = $this->getAuthCredential($credentials);
        $this->authCredentialManager->persist($authCredential);

        if (!$account = $authCredential->getAccount()) {
            $account = $this->accountManager->create();
            $account
                ->setEnabled(true)
                ->addAuthCredential($authCredential)
            ;
        }

        $this->accountManager->update($account);

        return $account;
    }

    /**
     * @param mixed $credentials
     *
     * @return AuthCredential
     */
    abstract public function getAuthCredential($credentials): AuthCredential;
}
