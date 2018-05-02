<?php

namespace App\Repository;

use App\Entity\Magistrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Magistrat|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Magistrat|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Magistrat[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class MagistratRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Magistrat::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('m')
            ->join('m.grade', 'mGrade')
            ->join('m.user', 'user')
            ->select('user.name, user.firstName, mGrade.abrv as grade, user.lastActivity, user.blocked, m.id')
            ->orderBy('mGrade.id', 'DESC')
            ->getQuery()->getArrayResult();
    }
}
