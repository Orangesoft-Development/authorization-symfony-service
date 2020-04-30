<?php

namespace App\Security\Guard;

use App\DBAL\Types\AuthMethodType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticatorFactory
{
    /**
     * @var PhoneAuthenticator
     */
    private $phoneAuthenticator;

    /**
     * @var FacebookAuthenticator
     */
    private $facebookAuthenticator;

    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    /**
     * @var AppleIdAuthenticator
     */
    private $appleIdAuthenticator;

    /**
     * AuthenticatorFactory constructor.
     *
     * @param PhoneAuthenticator $phoneAuthenticator
     * @param FacebookAuthenticator $facebookAuthenticator
     * @param GoogleAuthenticator $googleAuthenticator
     * @param AppleIdAuthenticator $appleIdAuthenticator
     */
    public function __construct(
        PhoneAuthenticator $phoneAuthenticator,
        FacebookAuthenticator $facebookAuthenticator,
        GoogleAuthenticator $googleAuthenticator,
        AppleIdAuthenticator $appleIdAuthenticator
    ) {
        $this->phoneAuthenticator = $phoneAuthenticator;
        $this->facebookAuthenticator = $facebookAuthenticator;
        $this->googleAuthenticator = $googleAuthenticator;
        $this->appleIdAuthenticator = $appleIdAuthenticator;
    }

    /**
     * @param Request $request
     *
     * @return AbstractLoginAuthenticator
     */
    public function get(Request $request): AbstractLoginAuthenticator
    {
        switch (true) {
            case $this->phoneAuthenticator->supports($request):
                return $this->phoneAuthenticator;
            case $this->facebookAuthenticator->supports($request):
                return $this->facebookAuthenticator;
            case $this->googleAuthenticator->supports($request):
                return $this->googleAuthenticator;
            case $this->appleIdAuthenticator->supports($request):
                return $this->appleIdAuthenticator;
            default:
                throw new AuthenticationException(
                    sprintf(
                        'Method must be one of %s, %s, %s, %s',
                        AuthMethodType::FACEBOOK,
                        AuthMethodType::PHONE,
                        AuthMethodType::GOOGLE,
                        AuthMethodType::APPLE
                    )
                );
        }
    }
}
