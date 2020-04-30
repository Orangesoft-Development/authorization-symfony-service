<?php

namespace App\Tests\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\SessionManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class SessionManagerTest extends TestCase
{
    /**
     * @var SessionRepository|MockObject
     */
    private $sessionRepository;

    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManager;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    public function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepository::class);
        $this->entityManager = $this->getEntityManager($this->sessionRepository);

        $parameterBag = new ParameterBag([
            'jwt_authentication' => [
                'refresh_token_ttl' => 3600,
            ],
        ]);

        $this->sessionManager = new SessionManager($this->entityManager, $parameterBag);
    }

    public function testFindSessionBy(): void
    {
        $session = $this->getSession();
        $criteria = [
            'id' => $session->getId(),
        ];

        $this->sessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($session)
        ;

        $this->assertEquals($session, $this->sessionManager->findSessionBy($criteria));
    }

    public function testFindSessionByAccessToken(): void
    {
        $session = $this->getSession();

        $accessToken = $session->getAccessToken();
        $criteria = [
            'accessToken' => $accessToken,
        ];

        $this->sessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($session)
        ;

        $this->assertEquals($session, $this->sessionManager->findSessionByAccessToken($accessToken));
    }

    public function testFindSessionByRefreshToken(): void
    {
        $session = $this->getSession();

        $refreshToken = $session->getRefreshToken();
        $criteria = [
            'refreshToken' => $refreshToken,
        ];

        $this->sessionRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($session)
        ;

        $this->assertEquals($session, $this->sessionManager->findSessionByRefreshToken($refreshToken));
    }

    public function testIsSessionExistBy(): void
    {
        $criteria = [
            'id' => 1,
        ];

        $this->sessionRepository
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn(1)
        ;

        $this->assertTrue($this->sessionManager->isExistsSessionBy($criteria));
    }

    public function testFindSessions(): void
    {
        $session = $this->getSession();
        $sessions = [$session];

        $this->sessionRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($sessions)
        ;

        $this->assertEquals($sessions, $this->sessionManager->findSessions());
    }

    /**
     * @param SessionRepository|MockObject $sessionRepository
     *
     * @return EntityManagerInterface|MockObject
     */
    private function getEntityManager(MockObject $sessionRepository): MockObject
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(Session::class)
            ->willReturn($sessionRepository)
        ;

        return $entityManager;
    }

    /**
     * @return Session|MockObject
     */
    private function getSession(): MockObject
    {
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);

        $session
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        $session
            ->expects($this->any())
            ->method('getAccessToken')
            ->willReturn('access_token')
        ;

        $session
            ->expects($this->any())
            ->method('getRefreshToken')
            ->willReturn('refresh_token')
        ;

        return $session;
    }
}
