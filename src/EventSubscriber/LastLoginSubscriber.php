<?php

namespace App\EventSubscriber;

use App\DBAL\Types\AuthMethodType;
use App\Entity\Account;
use App\Service\AuthCredentialManager;
use App\Service\SmsCodeManager;
use App\Service\AccountManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LastLoginSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * @var AuthCredentialManager
     */
    protected $authCredentialManager;

    /**
     * @var SmsCodeManager
     */
    protected $smsCodeManager;

    /**
     * LastLoginSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     * @param SmsCodeManager $smsCodeManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AccountManager $accountManager,
        AuthCredentialManager $authCredentialManager,
        SmsCodeManager $smsCodeManager
    ) {
        $this->entityManager = $entityManager;
        $this->accountManager = $accountManager;
        $this->authCredentialManager = $authCredentialManager;
        $this->smsCodeManager = $smsCodeManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     *
     * @throws Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $account = $event->getAuthenticationToken()->getUser();

        if (!$account instanceof Account) {
            return;
        }
        $account->setLastLogin(new DateTime());

        $credential = $this->authCredentialManager->findCredentialBy([
            'method' => AuthMethodType::PHONE,
            'account' => $account->getId(),
        ]);

        $this->accountManager->update($account, null === $credential);

        if (null === $credential) {
            return;
        }

        $smsCode = $this->smsCodeManager->findSmsCodeByPhone($credential->getUsername());

        if (null === $smsCode) {
            $this->entityManager->flush();

            return;
        }

        $smsCode
            ->setSmsCode(null)
            ->setSalt(null)
            ->setSmsCodeSentAt(null)
            ->setSmsCodeSentNumber(0)
        ;
        $this->smsCodeManager->update($smsCode);
    }
}
