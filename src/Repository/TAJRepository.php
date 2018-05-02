<?php

namespace App\Repository;

use App\Entity\Criminel;
use App\Entity\TAJ;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TAJ|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method TAJ|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method TAJ[]    findAll()
 * @method TAJ[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class TAJRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TAJ::class);
    }

    public function findByCriminel(Criminel $criminel, $dayLimit, $limit = 20)
    {
        $qb = $this->createQueryBuilder('taj');
        $qb->join('taj.criminels', 'crim');
        $qb->where('crim = :criminel')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), taj.updatedDate) < :dayLimit')
            ->setParameter('criminel', $criminel)
            ->setParameter('dayLimit', $dayLimit)
            ->orderBy('taj.updatedDate', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

}
