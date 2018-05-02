<?php

namespace App\Repository;

use App\Entity\Gardien;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Gardien|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Gardien|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Gardien[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class GardienRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Gardien::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('g')
            ->join('g.grade', 'grade')
            ->join('g.user', 'user')
            ->select('user.name, user.firstName, user.lastActivity, 
            grade.name AS gradeName, user.blocked, g.id')
            ->where('grade.category = :gradeCategory')
            ->setParameter('gradeCategory', 'Prison')
            ->orderBy('grade.id', 'DESC')
            ->getQuery()->getArrayResult();
    }
}
