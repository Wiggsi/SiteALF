<?php

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Grade|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Grade|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Grade[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 * @method Grade[]    findAll()
 */
class GradeRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    public function findByCategory($category)
    {
        return $this->createQueryBuilder('grade')
            ->select('grade.name, grade.abrv, grade.officier')
            ->where("grade.category = :category")->setParameter('category', $category)
            ->orderBy('grade.id', 'DESC')->getQuery()->getArrayResult();
    }
}
