<?php


namespace App\Repository;

use App\Entity\AuthCredential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AuthCredential|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthCredential|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthCredential[]    findAll()
 * @method AuthCredential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthCredentialRepository extends ServiceEntityRepository
{
    /**
     * SmsCodeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthCredential::class);
    }
}
