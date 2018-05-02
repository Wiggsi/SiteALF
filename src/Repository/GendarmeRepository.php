<?php

namespace App\Repository;

use App\Entity\Gendarme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Gendarme|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Gendarme|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Gendarme[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class GendarmeRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Gendarme::class);
    }

    public function findAll()
    {
        return $this->createQueryBuilder('gd')
            ->join('gd.grade', 'grade')
            ->join('gd.user', 'user')
            ->join('gd.unit', 'unit')
            ->select('user.name, user.firstName, user.lastActivity, 
            grade.name AS gradeName, unit.abrv AS unitAbrv, user.blocked, unit.id AS unitId, gd.id')
            ->where('grade.category = :gradeCategory')
            ->setParameter('gradeCategory', 'Gendarmerie')
            ->orderBy('grade.id', 'DESC')
            ->getQuery()->getArrayResult();
    }

    public function findConnected()
    {
        return $this->createQueryBuilder('gd')
            ->join('gd.grade', 'grade')
            ->join('gd.user', 'user')
            ->join('gd.unit', 'unit')
            ->select('user.name, user.firstName, user.lastActivity, 
            grade.name AS gradeName, unit.abrv AS unitAbrv, user.blocked, unit.id AS unitId, gd.id')
            ->where('grade.category = :gradeCategory')
            ->andWhere('user.lastActivity > :date')->setParameter('date', new \DateTime('-2min'))
            ->setParameter('gradeCategory', 'Gendarmerie')
            ->orderBy('grade.id', 'DESC')
            ->getQuery()->getArrayResult();
    }
}
