<?php

namespace App\Security\AccountProvider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface as BaseUserProviderInterface;

interface AccountProviderInterface extends BaseUserProviderInterface
{
    /**
     * @param int $id
     *
     * @return UserInterface|null
     */
    public function loadAccountById(int $id): ?UserInterface;
}
