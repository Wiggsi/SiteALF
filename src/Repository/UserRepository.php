<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method User|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class UserRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findNotRecruted()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.gendarme', 'gd')
            ->leftJoin('u.magistrat', 'm')
            ->leftJoin('u.gardien', 'ga')
            ->where('gd IS NULL')
            ->andWhere('m IS NULL')
            ->andWhere('ga IS NULL')
            ->orderBy('u.createdDateTime', 'DESC')
            ->setMaxResults(30)
            ->getQuery()->getResult();
    }
}
