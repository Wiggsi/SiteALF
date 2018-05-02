<?php

namespace App\Repository;

use App\Entity\Criminel;
use App\Entity\Gendarme;
use App\Entity\Magistrat;
use App\Entity\PV;
use App\Entity\Unit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method PV|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method PV|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method PV[]    findAll()
 * @method PV[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class PVRepository extends ServiceEntityRepository {
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PV::class);
    }

    public function findNonTermines(User $user, $result = TRUE, $pageLimit = 45, $page = 1)
    {
        $queryB = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit');

        if ($user->isGendarme()) {
            $queryB
                ->where('pv.visibility = :visibUnit AND gd.unit = :unit')
                ->orWhere('pv.visibility = :visibPerso AND gd = :user')
                ->orWhere('pv.visibility = :visibTous')
                ->setParameter('visibTous', 'Tous')
                ->setParameter('visibUnit', 'Unité')
                ->setParameter('visibPerso', 'Perso')
                ->setParameter('user', $user->getGendarme())
                ->setParameter('unit', $user->getGendarme()->getUnit());
        }
        else {
            $queryB
                ->where('pv.status = :transfere')
                ->orWhere('pv.status = :encours')
                ->orWhere('pv.status = :termine')
                ->setParameter('transfere', 'Transféré')
                ->setParameter('encours', 'En cours de jugement')
                ->setParameter('termine', "Terminé");
        }
        $queryB->andWhere("pv.status != 'Terminé'")
            ->orderBy('pv.updatedDate', 'DESC')
            ->orderBy('pv.status', 'ASC');

        if ($result) {
            $queryB
                ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
                ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
                ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName')
                ->setMaxResults($pageLimit)
                ->setFirstResult(($page - 1) * $pageLimit);

            $paginator = new Paginator($queryB);
            $paginator->setUseOutputWalkers(FALSE);

            return $paginator;
        }
        else
            return $queryB;
    }

    public function findByStatus(User $user, $status, $pageLimit = 45, $page = 1)
    {
        $qb = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit')
            ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
            ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
            ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName');

        if ($user->isGendarme()) {
            $qb
                ->where('pv.visibility = :visibUnit AND gd.unit = :unit')
                ->orWhere('pv.visibility = :visibPerso AND gd = :user')
                ->orWhere('pv.visibility = :visibTous')
                ->setParameter('visibTous', 'Tous')
                ->setParameter('visibUnit', 'Unité')
                ->setParameter('visibPerso', 'Perso')
                ->setParameter('user', $user->getGendarme())
                ->setParameter('unit', $user->getGendarme()->getUnit());
        }
        else {
            $qb
                ->where('pv.status = :transfere')
                ->orWhere('pv.status = :encours')
                ->orWhere('pv.status = :termine')
                ->setParameter('transfere', 'Transféré')
                ->setParameter('encours', 'En cours de jugement')
                ->setParameter('termine', "Terminé");
        }
        $qb->andWhere('pv.status = :val')->setParameter('val', $status)
            ->orderBy('pv.updatedDate', 'DESC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(FALSE);

        return $paginator;
    }

    public function findByCriminel(Criminel $criminel, User $user, $dayLimit, $pageLimit = 9, $page = 1)
    {
        $query = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit')
            ->join('pv.criminels', 'crim')
            ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
            ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
            ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName');

        if ($user->isGendarme()) {
            $query
                ->where('pv.visibility = :visibUnit AND gd.unit = :unit')
                ->orWhere('pv.visibility = :visibPerso AND gd = :user')
                ->orWhere('pv.visibility = :visibTous')
                ->setParameter('visibTous', 'Tous')
                ->setParameter('visibUnit', 'Unité')
                ->setParameter('visibPerso', 'Perso')
                ->setParameter('user', $user->getGendarme())
                ->setParameter('unit', $user->getGendarme()->getUnit());
        }
        else {
            $query
                ->where('pv.status = :transfere')
                ->orWhere('pv.status = :encours')
                ->orWhere('pv.status = :termine')
                ->setParameter('transfere', 'Transféré')
                ->setParameter('encours', 'En cours de jugement')
                ->setParameter('termine', "Terminé");
        }


        $query
            ->andWhere('crim = :criminel')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), pv.updatedDate) < :dayLimit')
            ->setParameter('criminel', $criminel)
            ->setParameter('dayLimit', $dayLimit)
            ->addSelect("(CASE 
        WHEN pv.status = 'Autre' THEN 0 
        WHEN pv.status = 'À modifier' THEN 1 
        WHEN pv.status = 'En cours' THEN 2
        WHEN pv.status = 'En cours de jugement' THEN 3
        WHEN pv.status = 'Transféré' THEN 4
        WHEN pv.status = 'Terminé' THEN 5
        ELSE 6 END) AS HIDDEN ORD ")
            ->orderBy('pv.updatedDate', 'DESC')
            ->orderBy('ORD', 'ASC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        $paginator = new Paginator($query);
        $paginator->setUseOutputWalkers(FALSE);

        return $paginator;
    }

    public function findByUnit(User $user, Unit $unit, $pageLimit = 9, $page = 1)
    {
        $qb = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit')
            ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
            ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
            ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName')->where('pv.visibility = :visibUnit AND gd.unit = :userUnit')
            ->orwhere('pv.visibility = :visibPerso AND gd = :user')
            ->orWhere('pv.visibility = :visibTous')
            ->andWhere('gd.unit = :unit')
            ->setParameter('visibTous', 'Tous')
            ->setParameter('visibUnit', 'Unité')
            ->setParameter('visibPerso', 'Perso')
            ->setParameter('user', $user->getGendarme())
            ->setParameter('userUnit', $user->getGendarme()->getUnit())
            ->setParameter('unit', $unit)
            ->addSelect("(CASE 
        WHEN pv.status = 'Autre' THEN 0 
        WHEN pv.status = 'À modifier' THEN 1 
        WHEN pv.status = 'En cours' THEN 2
        WHEN pv.status = 'En cours de jugement' THEN 3
        WHEN pv.status = 'Transféré' THEN 4
        WHEN pv.status = 'Terminé' THEN 5
        ELSE 6 END) AS HIDDEN ORD ")
            ->orderBy('pv.updatedDate', 'DESC')
            ->orderBy('ORD', 'ASC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(FALSE);

        return $paginator;
    }

    public function findByAuthor(User $user, Gendarme $author, $pageLimit = 9, $page = 1)
    {
        $qb = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit')
            ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
            ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
            ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName')->where('pv.visibility = :visibUnit AND gd.unit = :unit')
            ->orwhere('pv.visibility = :visibPerso AND gd = :user')
            ->orWhere('pv.visibility = :visibTous')
            ->andWhere('gd = :author')
            ->setParameter('visibTous', 'Tous')
            ->setParameter('visibUnit', 'Unité')
            ->setParameter('visibPerso', 'Perso')
            ->setParameter('user', $user->getGendarme())
            ->setParameter('unit', $user->getGendarme()->getUnit())
            ->setParameter('author', $author)
            ->addSelect("(CASE 
        WHEN pv.status = 'Autre' THEN 0 
        WHEN pv.status = 'À modifier' THEN 1 
        WHEN pv.status = 'En cours' THEN 2
        WHEN pv.status = 'En cours de jugement' THEN 3
        WHEN pv.status = 'Transféré' THEN 4
        WHEN pv.status = 'Terminé' THEN 5
        ELSE 6 END) AS HIDDEN ORD ")
            ->orderBy('pv.updatedDate', 'DESC')
            ->orderBy('ORD', 'ASC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);

        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(FALSE);

        return $paginator;
    }

    public function findByMagistrat(User $user, Magistrat $magistrat, $pageLimit = 9, $page = 1, $encours = FALSE)
    {
        $qb = $this->createQueryBuilder('pv')
            ->join('pv.author', 'gd')->join('gd.grade', 'gdGrade')->join('gd.user', 'gdUser')
            ->leftJoin('pv.magistrat', 'm')->leftJoin('m.grade', 'mGrade')->leftJoin('m.user', 'mUser')
            ->join('gd.unit', 'unit')
            ->select('pv.updatedDate, pv.numero, pv.resume, pv.visibility, pv.status, pv.id')
            ->addSelect('gdGrade.abrv AS authorGrade, unit.abrv AS authorUnit, gdUser.username as authorName, gd.id AS gdId')
            ->addSelect('mGrade.abrv AS magistratGrade, mUser.username AS magistratName');

        if ($user->isGendarme()) {
            $qb->where('pv.visibility = :visibUnit AND gd.unit = :unit')
                ->orwhere('pv.visibility = :visibPerso AND gd = :user')
                ->orWhere('pv.visibility = :visibTous')
                ->setParameter('visibTous', 'Tous')
                ->setParameter('visibUnit', 'Unité')
                ->setParameter('visibPerso', 'Perso')
                ->setParameter('user', $user->getGendarme())
                ->setParameter('unit', $user->getGendarme()->getUnit());
        }
        if ($encours) $qb->andWhere("pv.status != 'Terminé'");
        else if ($encours == NULL) $qb->andWhere("pv.status = 'Terminé'");
        $qb->andWhere('m = :magistrat')
            ->setParameter('magistrat', $magistrat)
            ->addSelect("(CASE 
        WHEN pv.status = 'Autre' THEN 0 
        WHEN pv.status = 'À modifier' THEN 1 
        WHEN pv.status = 'En cours' THEN 2
        WHEN pv.status = 'En cours de jugement' THEN 3
        WHEN pv.status = 'Transféré' THEN 4
        WHEN pv.status = 'Terminé' THEN 5
        ELSE 6 END) AS HIDDEN ORD ")
            ->orderBy('pv.updatedDate', 'DESC')
            ->orderBy('ORD', 'ASC')
            ->setMaxResults($pageLimit)
            ->setFirstResult(($page - 1) * $pageLimit);
        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(FALSE);

        return $paginator;
    }
}
