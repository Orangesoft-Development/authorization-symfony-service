<?php

namespace App\Service;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Repository\AuthCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class AuthCredentialManager
{
    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    /**
     * AuthCredentialManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->objectManager = $entityManager;
    }

    /**
     * @param AuthCredential $credential
     */
    public function delete(AuthCredential $credential): void
    {
        $this->objectManager->remove($credential);
        $this->objectManager->flush();
    }

    /**
     * @param array $criteria
     *
     * @return AuthCredential|object|null
     */
    public function findCredentialBy(array $criteria): ?AuthCredential
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function isExistsCredentialBy(array $criteria): bool
    {
        return $this->getRepository()->count($criteria) > 0;
    }

    /**
     * @return AuthCredential[]|object[]
     */
    public function findCredentials(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param AuthCredential $credential
     */
    public function persist(AuthCredential $credential): void
    {
        $this->objectManager->persist($credential);
    }

    /**
     * @param AuthCredential $credential
     */
    public function reload(AuthCredential $credential): void
    {
        $this->objectManager->refresh($credential);
    }

    /**
     * @param AuthCredential $credential
     * @param bool $andFlush
     */
    public function update(AuthCredential $credential, bool $andFlush = true): void
    {
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return AuthCredentialRepository|ObjectRepository
     */
    protected function getRepository(): AuthCredentialRepository
    {
        return $this->objectManager->getRepository(AuthCredential::class);
    }

    /**
     * @return AuthCredential
     */
    public function create(): AuthCredential
    {
        return new AuthCredential();
    }

    /**
     * @param string $phone
     *
     * @return AuthCredential|object|null
     */
    public function findCredentialByPhone(string $phone): ?AuthCredential
    {
        return $this->findCredentialBy([
            'method' => AuthMethodType::PHONE,
            'username' => $phone,
        ]);
    }

    /**
     * @param string $facebookId
     *
     * @return AuthCredential|object|null
     */
    public function findCredentialByFacebook(string $facebookId): ?AuthCredential
    {
        return $this->findCredentialBy([
            'method' => AuthMethodType::FACEBOOK,
            'username' => $facebookId,
        ]);
    }

    /**
     * @param string $googleId
     *
     * @return AuthCredential|object|null
     */
    public function findCredentialByGoogle(string $googleId): ?AuthCredential
    {
        return $this->findCredentialBy([
            'method' => AuthMethodType::GOOGLE,
            'username' => $googleId,
        ]);
    }

    /**
     * @param string $appleId
     *
     * @return AuthCredential|object|null
     */
    public function findCredentialByApple(string $appleId): ?AuthCredential
    {
        return $this->findCredentialBy([
            'method' => AuthMethodType::APPLE,
            'username' => $appleId,
        ]);
    }
}
