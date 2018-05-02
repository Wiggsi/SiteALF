<?php

namespace App\Repository;

use App\Entity\AppelCOG;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AppelCOG|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method AppelCOG|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method AppelCOG[]    findAll()
 * @method AppelCOG[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class AppelCOGRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AppelCOG::class);
    }

    public function findLatest($dayLimit = 2, $limit = 70)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), a.createdDate) < :dayLimit')
            ->setParameter('dayLimit', $dayLimit)
            ->orderBy('a.createdDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?AppelCOG
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
