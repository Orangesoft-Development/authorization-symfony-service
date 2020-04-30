<?php

namespace App\Security\AccountProvider;

use App\Entity\Account;
use App\Exception\AccountNotFoundException;
use App\Service\AccountManager;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountProvider implements AccountProviderInterface
{
    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * AccountProvider constructor.
     *
     * @param AccountManager $accountManager
     */
    public function __construct(AccountManager $accountManager)
    {
        $this->accountManager = $accountManager;
    }

    /**
     * @param string $username
     *
     * @return Account
     */
    public function loadUserByUsername($username): UserInterface
    {
        return $this->loadAccountById($username);
    }

    /**
     * @param int $id
     *
     * @return Account|UserInterface
     * @throws AccountNotFoundException
     */
    public function loadAccountById(int $id): UserInterface
    {
        if (!$account = $this->accountManager->findAccountBy(['id' => $id])) {
            throw new AccountNotFoundException(
                sprintf('Account with ID "%s" does not exist.', $id)
            );
        }

        return $account;
    }

    /**
     * @param Account|UserInterface $account
     *
     * @return Account
     */
    public function refreshUser(UserInterface $account): UserInterface
    {
        if (null === $reloadedAccount = $this->accountManager->findAccountBy(['id' => $account->getId()])) {
            throw new AccountNotFoundException(
                sprintf('Account with ID "%s" could not be reloaded.', $account->getId())
            );
        }

        return $reloadedAccount;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        $accountClass = Account::class;

        return $accountClass === $class || is_subclass_of($class, $accountClass);
    }
}
