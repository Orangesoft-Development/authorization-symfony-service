<?php

namespace App\Controller;

use App\Service\SessionManager;
use App\Service\SmsCodeManager;
use App\Service\SmsSender\SmsSenderInterface;
use App\Service\TokenExtractor\TokenExtractorInterface;
use App\Util\SmsCodeGenerator;
use DateTime;
use DomainException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Rest\Route("auth")
 */
class AuthController extends AbstractFOSRestController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SmsCodeManager
     */
    private $smsCodeManager;

    /**
     * @var SmsCodeGenerator
     */
    private $smsCodeGenerator;

    /**
     * @var SmsSenderInterface
     */
    private $smsSender;

    /**
     * @var TokenExtractorInterface
     */
    private $tokenExtractor;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * ResettingController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param SmsCodeManager $smsCodeManager
     * @param SmsCodeGenerator $smsCodeGenerator
     * @param SmsSenderInterface $smsSender
     * @param TokenExtractorInterface $tokenExtractor
     * @param SessionManager $sessionManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        SmsCodeManager $smsCodeManager,
        SmsCodeGenerator $smsCodeGenerator,
        SmsSenderInterface $smsSender,
        TokenExtractorInterface $tokenExtractor,
        SessionManager $sessionManager
    ) {
        $this->formFactory = $formFactory;
        $this->smsCodeManager = $smsCodeManager;
        $this->smsCodeGenerator = $smsCodeGenerator;
        $this->smsSender = $smsSender;
        $this->tokenExtractor = $tokenExtractor;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @Rest\Post("/send-sms")
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function sendSms(Request $request): View
    {
        $phone = $request->get('phone');

        $smsCode = $this->smsCodeManager->findSmsCodeByPhone($phone);

        if (null === $smsCode) {
            $smsCode = $this->smsCodeManager->create();
            $smsCode->setPhone($phone);
        }

        $retryTtl = $this->getParameter('sms_sending_retry_ttl');
        $retryNumber = $this->getParameter('sms_sending_retry_number');

        if (!$smsCode->isSmsCodeSendNonExpired($retryTtl)) {
            $smsCode
                ->setSmsCodeSentAt(new DateTime())
                ->setSmsCodeSentNumber(0)
            ;
        }

        if ($smsCode->getSmsCodeSentNumber() < $retryNumber) {
            if (null === ($code = $smsCode->getPlainSmsCode())) {
                $code = $this->smsCodeGenerator->generateSmsCode();

                $smsCode->setPlainSmsCode($code);
            }

            $this->smsSender->sendCode($smsCode);
        }

        $smsCode->incrementSmsCodeSentNumber();
        $this->smsCodeManager->update($smsCode);

        return View::create(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/login")
     *
     * @throws
     */
    public function login(): void
    {
        throw new DomainException('You should never see this');
    }

    /**
     * @Rest\Head("/refresh-token")
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function isExistsRefreshToken(Request $request): View
    {
        $accessToken = $this->tokenExtractor->extract($request);
        $isExistsSession = $this->sessionManager->isExistsSessionBy([
            'accessToken' => $accessToken,
        ]);

        if (!$isExistsSession) {
            throw new NotFoundHttpException('Session not found');
        }

        return View::create(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/refresh-token")
     *
     * @throws
     */
    public function refreshToken(): void
    {
        throw new DomainException('You should never see this');
    }

    /**
     * @Rest\Post("/logout")
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function logout(Request $request): View
    {
        $accessToken = $this->tokenExtractor->extract($request);
        $session = $this->sessionManager->findSessionByAccessToken($accessToken);

        if (null === $session) {
            throw new NotFoundHttpException("Token not found");
        }

        $this->sessionManager->delete($session);

        return View::create(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
