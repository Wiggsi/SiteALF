<?php

namespace App\Repository;

use App\Entity\Prison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Prison|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Prison|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Prison[]    findAll()
 * @method Prison[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class PrisonRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Prison::class);
    }

    public function findByType(array $types, $ended = FALSE, $enAttente = NULL, $limit = 100)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.criminel', 'c')
            ->select('p, c');

        foreach (range(0, count($types) - 1) as $i) {
            $param = 'type'.$i;
            $query->orWhere("p.type = :".$param)->setParameter($param, $types[$i]);
        }
        if ($enAttente !== NULL) $query->andWhere('p.enAttente = :attente')->setParameter('attente', $enAttente);

        return $query->andWhere('p.ended = :end')->setParameter('end', $ended)
            ->addOrderBy('p.type', 'DESC')
            ->addOrderBy('p.endDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
