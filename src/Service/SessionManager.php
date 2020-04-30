<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SessionManager
{
    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    /**
     * @var int
     */
    private $refreshTokenTtl;

    /**
     * RefreshTokenManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->objectManager = $entityManager;
        $this->refreshTokenTtl = $parameterBag->get('jwt_authentication')['refresh_token_ttl'];
    }

    /**
     * @param Session $session
     */
    public function delete(Session $session): void
    {
        $this->objectManager->remove($session);
        $this->objectManager->flush();
    }

    /**
     * @param array $criteria
     *
     * @return Session|object|null
     */
    public function findSessionBy(array $criteria): ?Session
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param string $accessToken
     *
     * @return Session|null
     */
    public function findSessionByAccessToken(string $accessToken): ?Session
    {
        return $this->findSessionBy([
            'accessToken' => $accessToken
        ]);
    }

    /**
     * @param string $refreshToken
     *
     * @return Session|null
     */
    public function findSessionByRefreshToken(string $refreshToken): ?Session
    {
        return $this->findSessionBy([
            'refreshToken' => $refreshToken
        ]);
    }

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function isExistsSessionBy(array $criteria): bool
    {
        return $this->getRepository()->count($criteria) > 0;
    }

    /**
     * @return Session[]|object[]
     */
    public function findSessions(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param Session $session
     */
    public function reload(Session $session): void
    {
        $this->objectManager->refresh($session);
    }

    /**
     * @param Session $session
     * @param bool $andFlush
     */
    public function update(Session $session, bool $andFlush = true): void
    {
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return SessionRepository|ObjectRepository
     */
    protected function getRepository(): SessionRepository
    {
        return $this->objectManager->getRepository(Session::class);
    }

    /**
     * @return Session
     *
     * @throws Exception
     */
    public function create(): Session
    {
        $datetime = new DateTime();
        $datetime->modify('+' . $this->refreshTokenTtl . ' seconds');
        $refreshToken = bin2hex(openssl_random_pseudo_bytes(64));

        $session = new Session();
        $session
            ->setRefreshToken($refreshToken)
            ->setValid($datetime)
        ;

        $this->objectManager->persist($session);

        return $session;
    }
}
