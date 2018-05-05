<?php

namespace App\Controller;

use App\Entity\Infraction;
use App\Entity\Magistrat;
use App\Entity\PV;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Repository\MagistratRepository;
use App\Repository\PrisonRepository;
use App\Repository\PVRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 *
 * @Route("/Tribunal")
 */
class TribunalController extends Controller {
    /**
     * @Route("", name="tribunal_homepage")
     */
    public function index(Request $request, PVRepository $rep)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findByMagistrat($this->getUser(), $this->getUser()->getMagistrat(), 21, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'tribunal_pv',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('Tribunal/index.html.twig', ['PVs' => $PVs, 'pagination' => $pagination]);
    }

    /**
     * @Route("/PVs/EnCours", name="tribunal_pv")
     */
    public function PVsEnCours(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findNonTermines($this->getUser(), TRUE, 21, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'tribunal_pv',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('Tribunal/pv.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'En cours',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PVs/Terminés", name="tribunal_pv_termines", options={"utf8": true})
     */
    public function PVsTermines(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findByStatus($this->getUser(), 'Terminé', 21, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'tribunal_pv',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('Tribunal/pv.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'Terminés',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PVs/{id}/EnCours", name="tribunal_pv_encours", requirements={"id"="\d+"})
     */
    public function PVEnCours(PV $pv)
    {
        $pv->setStatus('En cours de jugement');
        $pv->setMagistrat($this->getUser()->getMagistrat());
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('info', 'Le PV est en cours de jugement.');

        return $this->redirectToRoute('gd_pv_show', ['id' => $pv->getId()]);
    }

    /**
     * @Route("/PVs/{id}/Termine", name="tribunal_pv_termine", requirements={"id"="\d+"})
     */
    public function PVTermine(PV $pv)
    {
        $pv->setStatus('Terminé');
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Le PV est terminé !');

        return $this->redirectToRoute('gd_pv_show', ['id' => $pv->getId()]);
    }

    /**
     * @Route("Prison/EnAttente", name="tribunal_enattente")
     */
    public function enAttente(PrisonRepository $rep)
    {
        $prisons = $rep->findByType(["Bracelet électronique", "Prison", "Évadé", "Autre"], FALSE, TRUE);

        return $this->render("Tribunal/individus.html.twig", ['prisons' => $prisons, 'titre' => 'En attente de jugement']);
    }

    /**
     * @Route("Prison/Condamnés", name="tribunal_condamnes", options={"utf8": true})
     */
    public function condamnés(PrisonRepository $rep)
    {
        $prisons = $rep->findByType(["Bracelet électronique", "Prison", "Évadé", "Autre"], FALSE, FALSE);

        return $this->render("Tribunal/individus.html.twig", ['prisons' => $prisons, 'titre' => 'Condamnés']);
    }

    /**
     * @Route("/Personnel", name="tribunal_personnel")
     */
    public function showMagistrats(MagistratRepository $magistratRepository)
    {
        $magistrats = $magistratRepository->findAll();

        return $this->render('Tribunal/Personnel/magistrats.html.twig', ['magistrats' => $magistrats]);
    }

    /**
     * @Route("/Personnel/{id}", name="tribunal_personnel_show", requirements={"id"="\d+"})
     */
    public function showMagistrat(Magistrat $magistrat, PVRepository $repository, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        if (!is_null($this->getUser()) and ($this->getUser()->isMagistrat() or $this->getUser()->isGendarme())) {
            $PVs = $repository->findByMagistrat($this->getUser(), $magistrat, 9, $page);
        }
        else
            $PVs = [];

        $pagination = array(
            'page'         => $page,
            'route'        => 'tribunal_personnel_show',
            'pages_count'  => ceil(count($PVs) / 9),
            'route_params' => array('id' => $magistrat->getId()),
            'total'        => count($PVs),
        );

        return $this->render('Tribunal/Personnel/magistrat.html.twig', [
            'magistrat'  => $magistrat,
            'PVs'        => $PVs,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/Personnel/{id}/block", name="tribunal_personnel_block", requirements={"id"="\d+"})
     */
    public function blockMagistrat(Magistrat $magistrat)
    {
        if (!$this->getUser()->getMagistrat()->isOfficier()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour bloquer/débloquer ce magistrat.");
        }
        $magistrat->getUser()->setBlocked(!$magistrat->getUser()->getBlocked());
        $this->getDoctrine()->getManager()->flush();

        if ($magistrat->getUser()->getBlocked()) {
            $this->addFlash('warning', 'Le '.$magistrat.' a été bloqué.');
        }
        else {
            $this->addFlash('warning', 'Le '.$magistrat.' a été débloqué.');
        }

        return $this->redirectToRoute('tribunal_personnel_show', ['id' => $magistrat->getId()]);
    }

    /**
     * @Route("/Personnel/new", name="tribunal_personnel_new")
     */
    public function addMagistrat(UserRepository $rep)
    {
        if (!$this->getUser()->getMagistrat()->isOfficier())
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux magistrats.");

        $users = $rep->findNotRecruted();

        return $this->render("Tribunal/Personnel/new.html.twig", ['users' => $users]);
    }

    /**
     * @Route("/Personnel/new/{id}", name="tribunal_personnel_new_id", requirements={"id"="\d+"})
     */
    public function addMagistratID(User $user, UserRepository $repUser, GradeRepository $repGrade)
    {
        if (!$this->getUser()->getMagistrat()->isOfficier())
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux magistrats.");

        $magistrat = new Magistrat();
        $user->setRoles(['ROLE_USER', 'ROLE_TRIBUNAL']);
        $magistrat->setGrade($repGrade->findOneBy(['abrv' => 'Substitut']))
            ->setUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($magistrat);
        $em->flush();

        $this->addFlash('success', $user." est entré au Tribunal en tant que Substitut du Procureur.");

        return $this->redirectToRoute('tribunal_personnel_new');
    }
}
