<?php

namespace App\Repository;

use App\Entity\Unit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Unit|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Unit|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Unit[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 * @method Unit[]    findAll()
 */
class UnitRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    public function findByCategory(string $category)
    {
        return $this->createQueryBuilder('unit')
            ->select("unit.name, unit.abrv, unit.icon, unit.id")
            ->leftJoin('unit.gendarmes', 'gd')
            ->where('unit.category = :category')->setParameter('category', $category)
            ->addSelect('COUNT(gd.id) as nbGendarmes')->groupBy('unit.id')
            ->orderBy('unit.name', 'ASC')->getQuery()->getArrayResult();
    }
}
