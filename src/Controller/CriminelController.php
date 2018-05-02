<?php

namespace App\Controller;

use App\Entity\Criminel;
use App\Entity\PV;
use App\Entity\TAJ;
use App\Form\CriminelType;
use App\Repository\CriminelRepository;
use App\Repository\PVRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

const TIMELIMIT = 14; # Jours

/**
 * @Route("/TAJ")
 */
class CriminelController extends Controller {
    /**
     * @Route("", name="gd_taj")
     */
    public function index(CriminelRepository $rep)
    {
        $criminels = $rep->findAll();

        return $this->render('Crim/index.html.twig', [
            'criminels' => $criminels,
        ]);
    }

    /**
     * @Route("/new", name="gd_taj_new", methods="GET|POST")
     */
    public function new(Request $request)
    {
        $criminel = new Criminel();
        $form = $this->createForm(CriminelType::class, $criminel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($criminel);
            $em->flush();
            $this->addFlash('success', "Fiche de ".$criminel.' créée.');

            return $this->redirectToRoute('gd_taj_show', ['id' => $criminel->getId()]);
        }

        return $this->render('Crim/new.html.twig',
                             ['criminel' => $criminel,
                              'form'     => $form->createView(),]);
    }

    /**
     * @Route("/{id}", name="gd_taj_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(Criminel $criminel, PVRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $PVs = $rep->findByCriminel($criminel, $this->getUser(), TIMELIMIT, 9, $page);
        $TAJs = $this->getDoctrine()->getRepository(TAJ::class)->findByCriminel($criminel, TIMELIMIT);
        $pagination = array(
            'page'         => $page,
            'route'        => 'gd_taj_show',
            'pages_count'  => ceil(count($PVs) / 9),
            'route_params' => array('id' => $criminel->getId()),
        );

        return $this->render('Crim/show.html.twig', [
            'criminel'   => $criminel,
            'PVs'        => $PVs,
            'TAJs'       => $TAJs,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gd_taj_edit", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function edit(Request $request, Criminel $criminel)
    {
        if (!$this->getUser()->isGendarme())
            throw $this->createAccessDeniedException("Vous n'êtes pas gendarme.");

        $form = $this->createForm(CriminelType::class, $criminel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($criminel);
            $em->flush();
            $this->addFlash('info', "Fiche de ".$criminel." modifiée.");

            return $this->redirectToRoute('gd_taj_show', ['id' => $criminel->getId()]);
        }

        return $this->render('Crim/edit.html.twig', [
            'criminel' => $criminel,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_taj_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, Criminel $criminel)
    {
        if (!$this->getUser()->isGendarme() or !$this->getUser()->getGendarme()->isUnit('SR')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires à la suppression de cet individu.");
        }

        if ($this->isCsrfTokenValid('delete'.$criminel->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($criminel);
            $em->flush();

            $this->addFlash('info', "Fiche de ".$criminel." supprimée.");
        }

        return $this->redirectToRoute('gd_pvs');
    }
}
