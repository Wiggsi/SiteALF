<?php

namespace App\Controller;

use App\Entity\Gardien;
use App\Entity\User;
use App\Repository\GardienRepository;
use App\Repository\GradeRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/Prison")
 */
class APController extends Controller {
    /**
     * @Route("", name="prison_homepage")
     */
    public function index()
    {
        return $this->render('Prison/index.html.twig', [
        ]);
    }

    /**
     * @Route("/Personnel", name="prison_personnel")
     */
    public function showGardiens(GardienRepository $gardienRepository, GradeRepository $gradeRepository)
    {
        $gardiens = $gardienRepository->findAll();
        $grades = $gradeRepository->findByCategory('Prison');

        return $this->render('Prison/Personnel/gardiens.html.twig', ['gardiens' => $gardiens, 'grades' => $grades]);
    }

    /**
     * @Route("/Personnel/{id}", name="prison_personnel_show", requirements={"id"="\d+"})
     */
    public function showGardien(Gardien $gardien)
    {
        return $this->render('Prison/Personnel/gardien.html.twig', ['gardien' => $gardien]);
    }

    /**
     * @Route("/Personnel/{id}/promote", name="prison_personnel_promote", requirements={"id"="\d+"})
     */
    public function promoteGardien(Gardien $gardien, GradeRepository $rep)
    {
        if (!$this->getUser()->getGardien()->isOfficier() and
            $this->getUser()->getGardien()->getGrade()->getId() <= $gardien->getGrade()->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour promouvoir ce gardien.");
        }

        $nouveauGrade = $rep->findOneBy(['id' => $gardien->getGrade()->getId() + 1]);
        if ($nouveauGrade != NULL and $nouveauGrade->getCategory() == 'Prison') {
            $gardien->setGrade($nouveauGrade);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le '.$gardien.' a été promu '.$nouveauGrade.' .');

        }
        else {
            $this->addFlash('error', 'Impossible de promouvoir ce gardien.');
        }

        return $this->redirectToRoute('prison_personnel_show', ['id' => $gardien->getId()]);
    }

    /**
     * @Route("/Personnel/{id}/block", name="prison_personnel_block", requirements={"id"="\d+"})
     */
    public function blockGendarme(Gardien $gardien)
    {
        if (!$this->getUser()->getGardien()->isOfficier() and
            $this->getUser()->getGardien()->getGrade()->getID() < $gardien->getGrade()->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour bloquer/débloquer ce gendarme");
        }
        $gardien->getUser()->setBlocked(!$gardien->getUser()->getBlocked());
        $this->getDoctrine()->getManager()->flush();

        if ($gardien->getUser()->getBlocked()) {
            $this->addFlash('warning', 'Le '.$gardien.' a été bloqué.');
        }
        else {
            $this->addFlash('warning', 'Le '.$gardien.' a été débloqué.');
        }

        return $this->redirectToRoute('prison_personnel_show', ['id' => $gardien->getId()]);
    }

    /**
     * @Route("/Personnel/new", name="prison_personnel_new")
     */
    public function addGardien(UserRepository $rep)
    {
        if (!$this->getUser()->getGardien()->isOfficier()) {
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux gardiens");
        }

        $users = $rep->findNotRecruted();

        return $this->render("Prison/Personnel/new.html.twig", ['users' => $users]);
    }

    /**
     * @Route("/Personnel/new/{id}", name="prison_personnel_new_id", requirements={"id"="\d+"})
     */
    public function addGardienID(User $user, UserRepository $repUser, GradeRepository $repGrade)
    {
        if (!$this->getUser()->getGardien()->isOfficier()) {
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux gardiens");
        }
        $gardien = new Gardien();
        $user->setRoles(['ROLE_USER', 'ROLE_PRISON']);
        $gardien->setGrade($repGrade->findOneBy(['abrv' => 'Surveillant']))
            ->setUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($gardien);
        $em->flush();

        $this->addFlash('success', $user." est entré dans l'Administration Pénitentiaire au grade de surveillant.");

        return $this->redirectToRoute('prison_personnel_new');
    }
}
