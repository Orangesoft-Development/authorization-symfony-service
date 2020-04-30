<?php

namespace App\Service;

use App\Entity\SmsCode;
use App\Repository\SmsCodeRepository;
use App\Util\SmsCodeUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SmsCodeManager
{
    /**
     * @var SmsCodeUpdater
     */
    private $smsCodeUpdater;

    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    /**
     * SmsCodeManager constructor.
     *
     * @param SmsCodeUpdater $smsCodeUpdater
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SmsCodeUpdater $smsCodeUpdater, EntityManagerInterface $entityManager)
    {
        $this->smsCodeUpdater = $smsCodeUpdater;
        $this->objectManager = $entityManager;
    }

    /**
     * @param SmsCode $smsCode
     */
    public function delete(SmsCode $smsCode): void
    {
        $this->objectManager->remove($smsCode);
        $this->objectManager->flush();
    }

    /**
     * @param array $criteria
     *
     * @return SmsCode|object|null
     */
    public function findSmsCodeBy(array $criteria): ?SmsCode
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function isExistsSmsCodeBy(array $criteria): bool
    {
        return $this->getRepository()->count($criteria) > 0;
    }

    /**
     * @return SmsCode[]|object[]
     */
    public function findSmsCodes(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param SmsCode $smsCode
     */
    public function reload(SmsCode $smsCode): void
    {
        $this->objectManager->refresh($smsCode);
    }

    /**
     * @param SmsCode $smsCode
     * @param bool $andFlush
     */
    public function update(SmsCode $smsCode, bool $andFlush = true): void
    {
        $this->updateCode($smsCode);

        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return SmsCodeRepository|ObjectRepository
     */
    protected function getRepository(): SmsCodeRepository
    {
        return $this->objectManager->getRepository(SmsCode::class);
    }

    /**
     * @return SmsCode
     */
    public function create(): SmsCode
    {
        $smsCode = new SmsCode();

        $this->objectManager->persist($smsCode);

        return $smsCode;
    }

    /**
     * @param string $phone
     *
     * @return SmsCode|object|null
     */
    public function findSmsCodeByPhone(string $phone): ?SmsCode
    {
        return $this->findSmsCodeBy(['phone' => $phone]);
    }

    /**
     * @param SmsCode $smsCode
     */
    public function updateCode(SmsCode $smsCode)
    {
        $this->smsCodeUpdater->hashSmsCode($smsCode);
    }
}
