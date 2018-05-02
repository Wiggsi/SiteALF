<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Unit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Post|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class PostRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByUser(User $user, $pageLimit = 50, $page = 1)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.author', 'author')
            ->leftJoin('p.unit', 'unit')
            ->leftJoin('p.section', 'section')
            ->select('p, author, unit');
        if ($user->isGendarme()) {
            $qb->where('unit = :unit')
                ->orWhere('unit = :gd')
                ->orWhere('unit = :null')->setParameter('null', NULL)
                ->andWhere('section IN(:sections) OR section IS NULL')
                ->orWhere('author = :author')
                ->setParameter('unit', $user->getGendarme()->getUnit())
                ->setParameter('gd', $this->getEntityManager()->getRepository(Unit::class)->findOneBy(['abrv' => 'GD']))
                ->setParameter('author', $user)
                ->setParameter('sections', $user->getGendarme()->getSections());
        }
        //TODO: Faire JS pour afficher sélecteur unité/sections gD si public décoché
        //TODO: finirPostRepository pour AP + GD
        //TODO: vérifier si posts ublics OK
        //TODO: refaire Prison/posts pour AP
        //TODO: tester si tout marche
        else if ($user->isGardien()) {
            $qb->where('unit = :unit')
                ->orWhere('unit = :null')->setParameter('null', NULL)
                ->orWhere('author = :author')
                ->setParameter('unit', 'Prison')
                ->setParameter('author', $user);
        }
        $qb->orderBy('p.updatedDate', 'DESC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        return new Paginator($qb);
    }

    public function findByCategory(User $user, $category, $pageLimit = 50, $page = 1)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.unit = :unit')
            ->orWhere('p.unit = :gd')
            ->andWhere('p.section IN(:sections) OR p.section IS NULL')
            ->orWhere('p.author = :author')
            ->andWhere('p.category = :category')
            ->setParameter('unit', $user->getGendarme()->getUnit())
            ->setParameter('gd', $this->getEntityManager()->getRepository(Unit::class)->findOneBy(['abrv' => 'GD']))
            ->setParameter('author', $user)
            ->setParameter('sections', $user->getGendarme()->getSections())
            ->setParameter('category', $category)
            ->orderBy('p.updatedDate', 'DESC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        return new Paginator($qb);
    }
}
