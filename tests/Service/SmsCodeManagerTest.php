<?php

namespace App\Tests\Service;

use App\Entity\SmsCode;
use App\Repository\SmsCodeRepository;
use App\Service\SmsCodeManager;
use App\Util\SmsCodeUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SmsCodeManagerTest extends TestCase
{
    /**
     * @var SmsCodeRepository|MockObject
     */
    private $smsCodeRepository;

    /**
     * @var SmsCodeManager
     */
    private $smsCodeManager;

    protected function setUp()
    {
        $smsCodeUpdater = $this->getSmsCodeUpdater();

        $this->smsCodeRepository = $this->createMock(SmsCodeRepository::class);
        $entityManager = $this->getEntityManager($this->smsCodeRepository);

        $this->smsCodeManager = new SmsCodeManager($smsCodeUpdater, $entityManager);
    }

    public function testFindSmsCodeBy(): void
    {
        $smsCode = $this->getSmsCode();
        $criteria = [
            'id' => $smsCode->getId(),
        ];

        $this->smsCodeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($smsCode)
        ;

        $this->assertEquals($smsCode, $this->smsCodeManager->findSmsCodeBy($criteria));
    }

    public function testFindSmsCodeByPhone(): void
    {
        $smsCode = $this->getSmsCode();
        $phone = $smsCode->getPhone();
        $criteria = [
            'phone' => $phone,
        ];

        $this->smsCodeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($smsCode)
        ;

        $this->assertEquals($smsCode, $this->smsCodeManager->findSmsCodeByPhone($phone));
    }

    public function testIsSmsCodeExistBy(): void
    {
        $criteria = [
            'id' => 1,
        ];

        $this->smsCodeRepository
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn(1)
        ;

        $this->assertTrue($this->smsCodeManager->isExistsSmsCodeBy($criteria));
    }

    public function testFindSmsCodes(): void
    {
        $smsCode = $this->getSmsCode();
        $smsCodes = [$smsCode];

        $this->smsCodeRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($smsCodes)
        ;

        $this->assertEquals($smsCodes, $this->smsCodeManager->findSmsCodes());
    }

    /**
     * @param SmsCodeRepository|MockObject $smsCodeRepository
     *
     * @return EntityManagerInterface|MockObject
     */
    private function getEntityManager(MockObject $smsCodeRepository): MockObject
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(SmsCode::class)
            ->willReturn($smsCodeRepository)
        ;

        return $entityManager;
    }

    /**
     * @return SmsCodeUpdater|MockObject
     */
    private function getSmsCodeUpdater(): MockObject
    {
        return $this->createMock(SmsCodeUpdater::class);
    }

    /**
     * @return SmsCode|MockObject
     */
    private function getSmsCode(): MockObject
    {
        /** @var SmsCode|MockObject $session */
        $session = $this->createMock(SmsCode::class);

        $session
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1)
        ;

        $session
            ->expects($this->any())
            ->method('getPhone')
            ->willReturn('phone')
        ;

        return $session;
    }
}
