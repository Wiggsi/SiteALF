<?php

namespace App\Controller;

use App\Entity\TAJ;
use App\Form\TAJType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("/TAJ/Entrée", options={"utf8": true})
 */
class TAJController extends Controller {
    /**
     * @Route("/new", name="gd_taj_entry_new", methods="GET|POST")
     */
    public function new(Request $request)
    {
        $TAJ = new TAJ();
        $form = $this->createForm(TAJType::class, $TAJ);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()->isGendarme())
                $TAJ->setAuthor($this->getUser()->getGendarme());
            else
                $TAJ->setAuthor($TAJ->getPV()->getAuthor());

            $em = $this->getDoctrine()->getManager();
            $em->persist($TAJ);
            $em->flush();
            $this->addFlash('success', "Entrée au TAJ ".$TAJ." créée.");

            return $this->redirectToRoute('gd_taj_entry_show', ['id' => $TAJ->getId()]);
        }

        return $this->render('TAJ/new.html.twig',
                             ['TAJ'  => $TAJ,
                              'form' => $form->createView(),]);
    }

    /**
     * @Route("/{id}", name="gd_taj_entry_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(TAJ $TAJ)
    {
        return $this->render('TAJ/show.html.twig', [
            'TAJ' => $TAJ,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gd_taj_entry_edit", methods="GET|POST",
     *                      requirements={"id"="\d+"})
     */
    public function edit(Request $request, TAJ $TAJ)
    {
        if (!$this->getUser()->isGendarme() and !$this->getUser()->getGendarme()->isUnit('SR') and $TAJ->getAuthor() != $this->getUser()
                ->getGendarme()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires à la modification.");
        }

        $form = $this->createForm(TAJType::class, $TAJ);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $infractions = $TAJ->getInfractions();
            $infractions2 = [];
            foreach (range(0, count($infractions) - 1) as $i) {
                if ($infractions[$i] != "") $infractions2[] = $infractions[$i];
            }
            $TAJ->setInfractions($infractions2);
            $em = $this->getDoctrine()->getManager();
            $em->persist($TAJ);
            $em->flush();
            $this->addFlash('info', "Entrée au TAJ ".$TAJ." modifiée.");

            return $this->redirectToRoute('gd_taj_entry_show', ['id' => $TAJ->getId()]);
        }

        return $this->render('TAJ/edit.html.twig', [
            'TAJ'  => $TAJ,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_taj_entry_delete", methods="DELETE",
     *                 requirements={"id"="\d+"})
     */
    public function delete(Request $request, TAJ $TAJ)
    {
        if (!$this->getUser()->isGendarme() and !$this->getUser()->getGendarme()->isUnit('SR')) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires à la suppression.");
        }

        if ($this->isCsrfTokenValid('delete'.$TAJ->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($TAJ);
            $em->flush();

            $this->addFlash('info', "Entrée au TAJ ".$TAJ." supprimée.");
        }

        return $this->redirectToRoute('gd_taj');
    }
}
