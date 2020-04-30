<?php

namespace App\DataFixtures;

use App\Service\AccountManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AccountFixtures extends Fixture
{
    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * AccountFixtures constructor.
     *
     * @param AccountManager $accountManager
     */
    public function __construct(AccountManager $accountManager)
    {
        $this->accountManager = $accountManager;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load($manager): void
    {
        $account = $this->accountManager->create();
        $account
            ->setName('Victor')
            ->setEnabled(true)
        ;
        $this->accountManager->update($account);
    }
}
