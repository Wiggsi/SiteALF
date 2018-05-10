<?php

namespace App\Repository;

use App\Entity\Criminel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Criminel|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Criminel|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Criminel[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class CriminelRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Criminel::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.fichePrison', 'p')
            ->addSelect('c, p')
            ->addOrderBy('c.wanted', 'DESC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()->getResult();
    }
}