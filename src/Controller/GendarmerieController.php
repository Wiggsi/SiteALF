<?php

namespace App\Controller;

use App\Entity\Criminel;
use App\Entity\Section;
use App\Entity\User;
use App\Entity\Gendarme;
use App\Entity\Unit;
use App\Repository\GendarmeRepository;
use App\Repository\GradeRepository;
use App\Repository\PrisonRepository;
use App\Repository\PVRepository;
use App\Repository\SectionRepository;
use App\Repository\UnitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\PV;

/**
 * @Route("/Gendarmerie")
 */
class GendarmerieController extends Controller {
    /**
     * @Route("", name="gd_homepage")
     */
    public function index(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $query = $rep->createQueryBuilder('pv')
            ->where('pv.status != :termine')->setParameter('termine', "Terminé")
            ->andWhere('pv.author = :author')->setParameter('author', $this->getUser()->getGendarme())
            ->orderBy('pv.updatedDate', 'DESC')->orderBy('pv.status', 'ASC')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20);
        $PVs = new Paginator($query);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_homepage',
            'pages_count'  => ceil(count($PVs) / 30),
            'route_params' => array(),
        );

        return $this->render('Gendarmerie/index.html.twig', [
            'PVs'        => $PVs,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/GAV", name="gd_gav")
     */
    public function GAV(PrisonRepository $rep)
    {
        $GAVS = $rep->findByType(['GAV']);

        return $this->render('Gendarmerie/GAV.html.twig', [
            'gavs'       => $GAVS,
            'historique' => FALSE,
        ]);
    }

    /**
     * @Route("/GAV/Historique", name="gd_gav_historique")
     */
    public function GAVHistorique(PrisonRepository $rep)
    {
        $GAVS = $rep->findByType(['GAV'], TRUE);

        return $this->render('Gendarmerie/GAV.html.twig', [
            'gavs'       => $GAVS,
            'historique' => TRUE,
        ]);
    }


    /**
     * @Route("/Bracelets", name="gd_bracelet")
     */
    public function Bracelets(PrisonRepository $rep)
    {
        $bracelets = $rep->findByType(['Bracelet électronique']);

        return $this->render('Gendarmerie/bracelet.html.twig', [
            'bracelets' => $bracelets,
        ]);
    }

    /**
     * @Route("/Personnel", name="gd_personnel")
     */
    public function showGendarmes(GendarmeRepository $gendarmeRepository, UnitRepository $unitRepository, SectionRepository $sectionRepository, GradeRepository $gradeRepository)
    {
        $gendarmes = $gendarmeRepository->findAll();
        $units = $unitRepository->findByCategory('Gendarmerie');
        $sections = $sectionRepository->findByCategory('Gendarmerie');
        $grades = $gradeRepository->findByCategory('Gendarmerie');
        $nbConnected = count($gendarmeRepository->findConnected());

        return $this->render('Gendarmerie/Personnel/gendarmes.html.twig', [
            'gendarmes'   => $gendarmes,
            'units'       => $units,
            'sections'    => $sections,
            'grades'      => $grades,
            'nbConnected' => $nbConnected,
        ]);
    }

    /**
     * @Route("/Personnel/{id}", name="gd_personnel_show", requirements={"id"="\d+"})
     */
    public function showGendarme(Gendarme $gendarme, Request $request, PVRepository $PVrepository)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $PVrepository->findByAuthor($this->getUser(), $gendarme, 9, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_personnel_show',
            'pages_count'  => ceil(count($PVs) / 9),
            'route_params' => array('id' => $gendarme->getId()),
            'total'        => count($PVs),
        );

        return $this->render('Gendarmerie/Personnel/gendarme.html.twig', [
            'gendarme'   => $gendarme,
            'PVs'        => $PVs,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/Personnel/Unité/{id}", name="gd_personnel_unit", requirements={"id"="\d+"}, options={"utf8": true})
     */
    public function showUnit(Unit $unit, Request $request, PVRepository $rep)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findByUnit($this->getUser(), $unit, 9, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_personnel_unit',
            'pages_count'  => ceil(count($PVs) / 9),
            'route_params' => array('id' => $unit->getId()),
            'total'        => count($PVs),
        );

        return $this->render('Gendarmerie/Personnel/unit.html.twig', [
            'unit'       => $unit,
            'PVs'        => $PVs,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/Personnel/Section/{id}", name="gd_personnel_section", requirements={"id"="\d+"})
     */
    public function showSection(Section $section)
    {
        return $this->render('Gendarmerie/Personnel/section.html.twig', [
            'section' => $section,
        ]);
    }

    /**
     * @Route("/Personnel/{id}/unit/{idUnit}", name="gd_personnel_unit_enrole", requirements={"id"="\d+", "idUnit"="\d+"})
     */
    public function enroleUnitGendarme(Gendarme $gendarme, $idUnit, UnitRepository $repUnit)
    {
        $unit = $repUnit->findOneBy(['id' => $idUnit]);
        if (!$this->getUser()->getGendarme()->isChef() or $gendarme->isUnit($unit->getAbrv())) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour engager ce gendarme.");
        }

        $gendarme->setUnit($unit);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Le '.$gendarme.' a été intégré à l\'unité '.$unit);


        return $this->redirectToRoute('gd_personnel_show', ['id' => $gendarme->getId()]);
    }

    /**
     * @Route("/Personnel/{id}/section/{idSection}", name="gd_personnel_section_enrole", requirements={"id"="\d+",
     *     "idSection"="\d+"})
     */
    public function enroleSectionGendarme(Gendarme $gendarme, $idSection, SectionRepository $repSection)
    {
        $section = $repSection->findOneBy(['id' => $idSection]);
        if (!$this->getUser()->getGendarme()->isSection($section->getAbrv())) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter ce gendarme dans la section.");
        }

        $gendarme->addSection($section);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Le '.$gendarme.' a été ajouté à la section '.$section);


        return $this->redirectToRoute('gd_personnel_show', ['id' => $gendarme->getId()]);
    }

    /**
     * @Route("/Personnel/{id}/promote", name="gd_personnel_promote", requirements={"id"="\d+"})
     */
    public function promoteGendarme(Gendarme $gendarme, GradeRepository $rep)
    {
        if (!$this->getUser()->getGendarme()->isOfficier() and
            $this->getUser()->getGendarme()->getGrade()->getId() <= $gendarme->getGrade()->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour promouvoir ce gendarme.");
        }

        $nouveauGrade = $rep->findOneBy(['id' => $gendarme->getGrade()->getId() + 1]);
        if ($nouveauGrade != NULL and $nouveauGrade->getCategory() == 'Gendarmerie') {
            $gendarme->setGrade($nouveauGrade);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le '.$gendarme.' a été promu '.$nouveauGrade.' .');

        }
        else {
            $this->addFlash('error', 'Impossible de promouvoir ce gendarme.');
        }

        return $this->redirectToRoute('gd_personnel_show', ['id' => $gendarme->getId()]);
    }

    /**
     * @Route("/Personnel/{id}/opj", name="gd_personnel_opj", requirements={"id"="\d+"})
     */
    public function opjGendarme(Gendarme $gendarme, GradeRepository $rep)
    {
        if (!$this->getUser()->getGendarme()->isOfficier() and $this->getUser()->getGendarme() == $gendarme) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour mettre OPJ ce gendarme");
        }
        $gendarme->setOpj(TRUE);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Le '.$gendarme.' a obtenu son OPJ.');

        return $this->redirectToRoute('gd_personnel_show', ['id' => $gendarme->getId()]);
    }

    /**
     * @Route("/Personnel/{id}/block", name="gd_personnel_block", requirements={"id"="\d+"})
     */
    public function blockGendarme(Gendarme $gendarme)
    {
        if (!$this->getUser()->getGendarme()->isOfficier() and
            $this->getUser()->getGendarme()->getGrade()->getID() < $gendarme->getGrade()->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les autorisations pour bloquer/débloquer ce gendarme");
        }
        $gendarme->getUser()->setBlocked(!$gendarme->getUser()->getBlocked());
        $this->getDoctrine()->getManager()->flush();

        if ($gendarme->getUser()->getBlocked()) {
            $this->addFlash('warning', 'Le '.$gendarme.' a été bloqué.');
        }
        else {
            $this->addFlash('warning', 'Le '.$gendarme.' a été débloqué.');
        }

        return $this->redirectToRoute('gd_personnel_show', ['id' => $gendarme->getId()]);
    }

    /**
     * @Route("/Personnel/new", name="gd_personnel_new")
     */
    public function addGendarme(UserRepository $rep)
    {
        if (!$this->getUser()->getGendarme()->isSection('CNEFG')) {
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux gendarmes");
        }

        $users = $rep->findNotRecruted();

        return $this->render("Gendarmerie/Personnel/new.html.twig", ['users' => $users]);
    }

    /**
     * @Route("/Personnel/new/{id}", name="gd_personnel_new_id", requirements={"id"="\d+"})
     */
    public function addGendarmeID(User $user, UserRepository $repUser, GradeRepository $repGrade, UnitRepository $repUnit)
    {
        if (!$this->getUser()->getGendarme()->isSection('CNEFG')) {
            return $this->createAccessDeniedException("Vous n'avez pas les autorisations pour ajouter de nouveaux gendarmes");
        }
        $gendarme = new Gendarme();
        $user->setRoles(['ROLE_USER', 'ROLE_GENDARME']);
        $gendarme->setGrade($repGrade->findOneBy(['abrv' => 'EG']))
            ->setUnit($repUnit->findOneBy(['abrv' => 'GD']))
            ->setUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($gendarme);
        $em->flush();

        $this->addFlash('success', $user." est entré dans la Gendarmerie au grade d'élève-gendarme.");

        return $this->redirectToRoute('gd_personnel_new');
    }
}
