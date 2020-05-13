<?php

namespace App\Tests\Service;

use App\DBAL\Types\AuthMethodType;
use App\Entity\AuthCredential;
use App\Repository\AuthCredentialRepository;
use App\Service\AuthCredentialManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthCredentialManagerTest extends TestCase
{
    /**
     * @var AuthCredentialRepository|MockObject
     */
    private $authCredentialRepository;

    /**
     * @var AuthCredentialManager
     */
    private $authCredentialManager;

    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    protected function setUp(): void
    {
        $this->authCredentialRepository = $this->createMock(AuthCredentialRepository::class);
        $this->entityManager = $this->getEntityManager($this->authCredentialRepository);

        $this->authCredentialManager = new AuthCredentialManager($this->entityManager);
    }

    public function testFindCredentialBy(): void
    {
        $authCredential = new AuthCredential();
        $criteria = [
            'id' => 1,
        ];

        $this->authCredentialRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($authCredential)
        ;

        $this->assertEquals($authCredential, $this->authCredentialManager->findCredentialBy($criteria));
    }

    public function testIsCredentialExistBy(): void
    {
        $criteria = [
            'id' => 1,
        ];

        $this->authCredentialRepository
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn(1)
        ;

        $this->assertTrue($this->authCredentialManager->isExistsCredentialBy($criteria));
    }

    public function testFindCredentials(): void
    {
        $authCredentials = [new AuthCredential()];

        $this->authCredentialRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($authCredentials)
        ;

        $this->assertEquals($authCredentials, $this->authCredentialManager->findCredentials());
    }

    public function testFindCredentialByPhone(): void
    {
        $authCredential = new AuthCredential();
        $authCredential
            ->setMethod(AuthMethodType::PHONE)
            ->setUsername('phone')
        ;

        $this->createFindOneByMethod($authCredential);

        $this->assertEquals(
            $authCredential,
            $this->authCredentialManager->findCredentialByPhone($authCredential->getUsername())
        );
    }

    public function testFindCredentialByFacebook(): void
    {
        $authCredential = new AuthCredential();
        $authCredential
            ->setMethod(AuthMethodType::FACEBOOK)
            ->setUsername('facebook')
        ;

        $this->createFindOneByMethod($authCredential);

        $this->assertEquals(
            $authCredential,
            $this->authCredentialManager->findCredentialByFacebook($authCredential->getUsername())
        );
    }

    public function testFindCredentialByGoogle(): void
    {
        $authCredential = new AuthCredential();
        $authCredential
            ->setMethod(AuthMethodType::GOOGLE)
            ->setUsername('google')
        ;

        $this->createFindOneByMethod($authCredential);

        $this->assertEquals(
            $authCredential,
            $this->authCredentialManager->findCredentialByGoogle($authCredential->getUsername())
        );
    }

    public function testFindCredentialByApple(): void
    {
        $authCredential = new AuthCredential();
        $authCredential
            ->setMethod(AuthMethodType::APPLE)
            ->setUsername('apple')
        ;

        $this->createFindOneByMethod($authCredential);

        $this->assertEquals(
            $authCredential,
            $this->authCredentialManager->findCredentialByApple($authCredential->getUsername())
        );
    }

    /**
     * @param AuthCredential $authCredential
     */
    private function createFindOneByMethod(AuthCredential $authCredential): void
    {
        $this->authCredentialRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'method' => $authCredential->getMethod(),
                'username' => $authCredential->getUsername(),
            ])
            ->willReturn($authCredential)
        ;
    }

    /**
     * @param AuthCredentialRepository|MockObject $authCredentialRepository
     *
     * @return EntityManagerInterface|MockObject
     */
    private function getEntityManager(MockObject $authCredentialRepository): MockObject
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(AuthCredential::class)
            ->willReturn($authCredentialRepository)
        ;

        return $this->entityManager;
    }
}
