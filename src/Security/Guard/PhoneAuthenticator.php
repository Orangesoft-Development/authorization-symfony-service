<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Entity\SmsCode;
use App\Entity\Account;
use App\Security\Handler\AuthenticationFailureHandler;
use App\Security\Handler\AuthenticationSuccessHandler;
use App\Service\AuthCredentialManager;
use App\Service\SmsCodeManager;
use App\Service\AccountManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class PhoneAuthenticator extends AbstractLoginAuthenticator
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var SmsCodeManager
     */
    private $smsCodeManager;

    /**
     * PhoneAuthenticator constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param SmsCodeManager $smsCodeManager
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $authCredentialManager
     * @param AuthenticationSuccessHandler $successHandler
     * @param AuthenticationFailureHandler $failureHandler
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        SmsCodeManager $smsCodeManager,
        AccountManager $accountManager,
        AuthCredentialManager $authCredentialManager,
        AuthenticationSuccessHandler $successHandler,
        AuthenticationFailureHandler $failureHandler
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->smsCodeManager = $smsCodeManager;

        parent::__construct($accountManager, $authCredentialManager, $successHandler, $failureHandler);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->get('method') === AuthMethodType::PHONE;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getCredentials(Request $request): array
    {
        return [
            'phone' => $request->get('phone'),
            'sms_code' => $request->get('sms_code'),
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserInterface|Account $account
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $account): bool
    {
        if (!$presentedSmsCode = $credentials['sms_code']) {
            throw new BadCredentialsException('The presented password cannot be empty.');
        }

        $smsCode = $this->smsCodeManager->findSmsCodeByPhone($credentials['phone']);

        if (null === $smsCode) {
            throw new AuthenticationException(
                sprintf('No SMS code was sent to phone %s', $credentials['phone'])
            );
        }

        if (
            null === $smsCode->getSmsCode()
            || !$this->encoderFactory
                ->getEncoder(SmsCode::class)
                ->isPasswordValid(
                    $smsCode->getSmsCode(),
                    $presentedSmsCode,
                    $smsCode->getSalt()
                )
        ) {
            throw new BadCredentialsException('The presented sms code is invalid.');
        }

        $this->accountManager->update($account);

        return true;
    }

    /**
     * @param mixed $credentials
     *
     * @return AuthCredential
     */
    public function getAuthCredential($credentials): AuthCredential
    {
        $authCredential = $this->authCredentialManager->findCredentialByPhone($credentials['phone']);

        if (null === $authCredential) {
            $authCredential = $this->authCredentialManager->create();
            $authCredential
                ->setMethod(AuthMethodType::PHONE)
                ->setName($credentials['phone'])
                ->setUsername($credentials['phone'])
            ;
        }

        return $authCredential;
    }
}
