<?php

namespace App\Repository;

use App\Entity\SmsCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SmsCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmsCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmsCode[]    findAll()
 * @method SmsCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmsCodeRepository extends ServiceEntityRepository
{
    /**
     * SmsCodeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmsCode::class);
    }
}
