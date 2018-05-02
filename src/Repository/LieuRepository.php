<?php

namespace App\Repository;

use App\Entity\Lieu;
use App\Entity\Unit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Lieu|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Lieu|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Lieu[]    findAll()
 * @method Lieu[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class LieuRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function findByUnit(Unit $unit)
    {
        return $this->createQueryBuilder('l')
            ->join('l.units', 'u')
            ->select('l.name, u.abrv AS unit, l.x, l.y, l.icon, l.id')
            ->andWhere('u = :unit')
            ->orWhere('u.abrv = :gd')
            ->setParameter('unit', $unit)
            ->setParameter('gd', 'GD')
            ->orderBy('l.updatedDate', 'DESC')
            ->orderBy('unit', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
