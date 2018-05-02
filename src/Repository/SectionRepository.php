<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Section|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Section|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Section[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 * @method Section[]    findAll()
 */
class SectionRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Section::class);
    }

    public function findByCategory(string $category)
    {
        return $this->createQueryBuilder('section')
            ->leftJoin('section.gendarmes', 'gd')
            ->where('section.category = :category')->setParameter('category', $category)
            ->select("section.abrv, section.name, section.icon, section.id")
            ->addSelect('COUNT(gd.id) as nbGendarmes')->groupBy('section.id')
            ->orderBy('section.name', 'ASC')->getQuery()->getArrayResult();
    }
}
