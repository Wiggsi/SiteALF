<?php

namespace App\Controller;

use App\Entity\PV;
use App\Form\PVType;
use App\Repository\PVRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/Gendarmerie")
 */
class PVController extends Controller {
    /**
     * @Route("/PVs", name="gd_pvs")
     */
    public function index(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $query = $rep->createQueryBuilder('pv')->join('pv.author', 'author')
            ->join('pv.magistrat', 'm')
            ->join('m.grade', 'mg')->join('author.grade', 'gg')
            ->where('author = :gd')->setParameter('gd', $this->getUser()->getGendarme())
            ->orderBy('pv.updatedDate', 'DESC')->orderBy('pv.status', 'ASC')
            ->setFirstResult(($page - 1) * 21)->setMaxResults(21);
        $PVs = new Paginator($query);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_pvs',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('PV/index.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'Vos PVs',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PVs/OPJ", name="gd_pvs_opj")
     */
    public function PVsOPJ(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $query = $rep->createQueryBuilder('pv')->join('pv.author', 'author')
            ->join('pv.magistrat', 'm')
            ->join('m.grade', 'mg')->join('author.grade', 'gg')
            ->where('pv.OPJ = :gd')
            ->andWhere('pv.author != :gd')->setParameter('gd', $this->getUser()->getGendarme())
            ->orderBy('pv.updatedDate', 'DESC')->orderBy('pv.status', 'ASC')
            ->setFirstResult(($page - 1) * 21)->setMaxResults(21);
        $PVs = new Paginator($query);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_pvs_opj',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('PV/index.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'OPJ rérérent',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PVs/EnCours", name="gd_pvs_global")
     */
    public function PVsEnCours(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findNonTermines($this->getUser(), TRUE, 21, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_pvs_global',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('PV/index.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'En cours',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PVs/Terminés", name="gd_pvs_termines", options={"utf8": true})
     */
    public function PVsTermines(PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $PVs = $rep->findByStatus($this->getUser(), 'Terminé', 21, $page);

        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_pvs_termines',
            'pages_count'  => ceil(count($PVs) / 21),
            'route_params' => array(),
            'total'        => count($PVs),
        );

        return $this->render('PV/index.html.twig', [
            'PVs'        => $PVs,
            'titre'      => 'Terminés',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/PV/new", name="gd_pv_new", methods="GET|POST")
     */
    public function new(Request $request, PVRepository $repository)
    {
        $PV = new PV();
        $PV->setAuthor($this->getUser()->getGendarme());
        if ($this->getUser()->getGendarme()->getOPJ())
            $PV->setOPJ($this->getUser()->getGendarme());
        $num = $repository->findOneBy([], ['createdDate' => 'DESC'])->getNumero();
        $num = mb_split('/', $num)[0] + 1;
        $y = new \DateTime();
        $PV->setNumero((string)$num.'/'.$y->format('y'));
        $form = $this->createForm(PVType::class, $PV);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($PV);
            $em->flush();
            $this->addFlash('success', "PV n°".$PV->getNumero().' créé.');

            return $this->redirectToRoute('gd_pv_show', ['id' => $PV->getId()]);
        }

        return $this->render('PV/new.html.twig', [
            'PV'   => $PV,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/PV/{id}", name="gd_pv_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(PV $PV)
    {
        if ($this->getUser()->isMagistrat() and in_array($PV->getStatus(), ['En cours', 'À modifier', 'Autre'])) {
            $this->addFlash('warning', "Ce PV n'a pas encore été transféré au tribunal.");

            return $this->redirectToRoute('tribunal_enattente');
        }

        return $this->render('PV/show.html.twig', ['PV' => $PV]);
    }

    /**
     * @Route("/PV/{id}/edit", name="gd_pv_edit", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function edit(Request $request, PV $PV)
    {
        if ($PV->getAuthor() != $this->getUser()->getGendarme() and $PV->getOPJ() != $this->getUser()->getGendarme()
            and !$this->getUser()->getGendarme()->isUnit('SR') and !$this->getUser()->getGendarme()->isSection('PJGN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires pour modifier ce PV.");
        }
        $form = $this->createForm(PVType::class, $PV);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', "PV n°".$PV->getId().' modifié.');

            return $this->redirectToRoute('gd_pv_show', ['id' => $PV->getId()]);
        }

        return $this->render('PV/edit.html.twig', [
            'PV'   => $PV,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/PV/{id}", name="gd_pv_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, PV $PV)
    {
        if ($PV->getAuthor() != $this->getUser()->getGendarme() and $PV->getOPJ() != $this->getUser()->getGendarme()
            and !$this->getUser()->getGendarme()->isUnit('SR')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires pour modifier ce PV.");
        }
        if ($this->isCsrfTokenValid('delete'.$PV->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($PV);
            $em->flush();
            $this->addFlash('info', "PV n°".$PV->getId().' supprimé.');
        }

        return $this->redirectToRoute('gd_pvs');
    }
}
