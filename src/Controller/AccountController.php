<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\AuthCredential;
use App\Form\AccountFormType;
use App\Security\Guard\AuthenticatorFactory;
use App\Service\AccountManager;
use App\Service\AuthCredentialManager;
use App\Service\FileManager\FileManagerInterface;
use App\Storage\StreamedFile;
use App\Validator\StreamFile;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Rest\Route("accounts")
 */
class AccountController extends AbstractFOSRestController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var AuthCredentialManager
     */
    private $authenticatorFactory;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var AuthCredentialManager
     */
    private $credentialManager;

    /**
     * @var FileManagerInterface
     */
    private $fileManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * AccountController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param AuthenticatorFactory $authenticatorFactory
     * @param AccountManager $accountManager
     * @param AuthCredentialManager $credentialManager
     * @param FileManagerInterface $fileManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        AuthenticatorFactory $authenticatorFactory,
        AccountManager $accountManager,
        AuthCredentialManager $credentialManager,
        FileManagerInterface $fileManager,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->authenticatorFactory = $authenticatorFactory;
        $this->accountManager = $accountManager;
        $this->credentialManager = $credentialManager;
        $this->fileManager = $fileManager;
        $this->validator = $validator;
    }

    /**
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     *
     * @param Account $account
     *
     * @return View
     *
     * @throws
     */
    public function show(Account $account): View
    {
        $accountDTO = $this->accountManager->createDTO($account);

        return View::create($accountDTO, JsonResponse::HTTP_OK);
    }

    /**
     * @Rest\Patch("/{id}", requirements={"id"="\d+"})
     *
     * @param Account $account
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function edit(Account $account, Request $request): View
    {
        $form = $this->formFactory
            ->createNamed('', AccountFormType::class, $account, [
                'method' => $request->getMethod(),
            ])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->accountManager->update($account);

            $accountDTO = $this->accountManager->createDTO($account);

            return View::create($accountDTO, JsonResponse::HTTP_OK);
        }

        return View::create($form, JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param Account $account
     *
     * @return View
     */
    public function delete(Account $account): View
    {
        if (($avatarUrl = $account->getAvatarUrl()) && $this->fileManager->exists($avatarUrl)) {
            $this->fileManager->remove($avatarUrl);
        }

        $this->accountManager->delete($account);

        return View::create(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/{id}/avatar", requirements={"id"="\d+"})
     *
     * @param Account $account
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function createAvatar(Account $account, Request $request): View
    {
        $path = 'accounts/' . $account->getId() . '/' . time();
        $avatar = $request->getContent(true);
        $avatarStream = new StreamedFile($path, $avatar);

        /** @var ConstraintViolationList $violationList */
        $violationList = $this->validator->validate(
            $avatarStream,
            new StreamFile([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/*',
                ],
            ])
        );
        if ($violationList->count() > 0) {
            throw new BadRequestHttpException($violationList[0]->getMessage());
        }

        if (($avatarUrl = $account->getAvatarUrl()) && $this->fileManager->exists($avatarUrl)) {
            $this->fileManager->remove($avatarUrl);
        }

        $this->fileManager->uploadStream($avatarStream);

        $account->setAvatarUrl($avatarStream->getPath());
        $this->accountManager->update($account);

        $accountDTO = $this->accountManager->createDTO($account);

        return View::create($accountDTO, JsonResponse::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/{id}/avatar", requirements={"id"="\d+"})
     *
     * @param Account $account
     *
     * @return View
     *
     * @throws
     */
    public function deleteAvatar(Account $account): View
    {
        if ($avatarUrl = $account->getAvatarUrl()) {
            $this->fileManager->remove($avatarUrl);
        }

        $account->setAvatarUrl(null);
        $this->accountManager->update($account);

        $accountDTO = $this->accountManager->createDTO($account);

        return View::create($accountDTO, JsonResponse::HTTP_OK);
    }

    /**
     * @Rest\Post("/{id}/auth-credentials", requirements={"id"="\d+"})
     *
     * @param Account $account
     * @param Request $request
     *
     * @return View
     *
     * @throws
     */
    public function createAuthCredential(Account $account, Request $request): View
    {
        $authenticator = $this->authenticatorFactory->get($request);

        $credentials = $authenticator->getCredentials($request);
        if (!$authenticator->checkCredentials($credentials, $account)) {
            throw new BadRequestHttpException('Bad auth credentials');
        }

        $methodExpr = new Comparison('method', '=', $request->get('method'));
        $criteria = Criteria::create()
            ->where($methodExpr)
            ->setMaxResults(1)
        ;

        /** @var AuthCredential|bool $accountAuthCredential */
        $accountAuthCredential = $account
            ->getAuthCredentials()
            ->matching($criteria)
            ->first()
        ;

        $requestAuthCredential = $authenticator->getAuthCredential($credentials);
        $entityManager = $this->getDoctrine()->getManager();

        if (
            $entityManager->contains($requestAuthCredential)
            && (
                $accountAuthCredential === false
                || $accountAuthCredential->getId() != $requestAuthCredential->getId()
            )
        ) {
            throw new ConflictHttpException('These credentials are used by other account');
        }

        if ($accountAuthCredential === false) {
            $this->credentialManager->persist($requestAuthCredential);
            $account->addAuthCredential($requestAuthCredential);
        } else {
            $accountAuthCredential
                ->setUsername($requestAuthCredential->getUsername())
                ->setName($requestAuthCredential->getName())
            ;
            $this->credentialManager->update($accountAuthCredential, false);
        }

        $this->accountManager->update($account);

        $accountDTO = $this->accountManager->createDTO($account);

        return View::create($accountDTO, JsonResponse::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/{id}/auth-credentials/{method}", requirements={
     *     "id"="\d+",
     *     "method"="phone|facebook|google|apple"
     * })
     *
     * @param Account $account
     * @param string $method
     *
     * @return View
     *
     * @throws
     */
    public function deleteAuthCredential(Account $account, string $method): View
    {
        if ($account->getAuthCredentials()->count() == 1) {
            throw new AccessDeniedHttpException('You cannot delete a single auth credential');
        }

        $methodExpr = new Comparison('method', '=', $method);
        $criteria = Criteria::create()
            ->where($methodExpr)
            ->setMaxResults(1)
        ;

        /** @var AuthCredential|bool $accountAuthCredential */
        $accountAuthCredential = $account
            ->getAuthCredentials()
            ->matching($criteria)
            ->first()
        ;

        if ($accountAuthCredential !== false) {
            $account->removeAuthCredential($accountAuthCredential);
            $this->accountManager->update($account);
        }

        $accountDTO = $this->accountManager->createDTO($account);

        return View::create($accountDTO, JsonResponse::HTTP_OK);
    }
}
