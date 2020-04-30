<?php

namespace App\Command;

use App\DBAL\Types\AuthMethodType;
use App\Service\AuthCredentialManager;
use App\Service\AccountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAccountCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'create:account';

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var AuthCredentialManager
     */
    private $authCredentialManager;

    /**
     * CreateAccountCommand constructor.
     *
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     */
    public function __construct(AccountManager $accountManager, AuthCredentialManager $authCredentialManager) {
        $this->accountManager = $accountManager;
        $this->authCredentialManager = $authCredentialManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Create a new account.')
            ->setHelp('This command allows you to create a account.')
            ->addArgument('phone', InputArgument::REQUIRED, 'Unique phone for account.')
            ->addArgument('name', InputArgument::REQUIRED, 'Unique name for account.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $phone = $input->getArgument('phone');
        $isExistsCredential = $this->authCredentialManager->isExistsCredentialBy([
            'method' => AuthMethodType::PHONE,
            'username' => $phone,
        ]);

        if ($isExistsCredential) {
            $io->warning(sprintf('Admin with phone "%s" exist.', $phone));

            return;
        }

        $name = $input->getArgument('name');

        $account = $this->accountManager->create();
        $account
            ->setName($name)
            ->setEnabled(true)
        ;

        $credential = $this->authCredentialManager->create();
        $credential
            ->setAccount($account)
            ->setMethod(AuthMethodType::PHONE)
            ->setUsername($phone)
        ;

        $this->accountManager->update($account, false);
        $this->authCredentialManager->update($credential);

        $io->success(sprintf('Account @%s (%s) successful created!', $phone, $name));
    }
}
