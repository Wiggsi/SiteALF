<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Unit;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("/Gendarmerie/Carte")
 */
class LieuController extends Controller {
    /**
     * @Route("", name="gd_carte")
     */
    public function carte()
    {
        $repository = $this->getDoctrine()->getRepository(Lieu::class);
        $lieux = $repository->findByUnit($this->getUser()->getGendarme()->getUnit());

        return $this->render('Gendarmerie/carte.html.twig', ['lieux' => $lieux]);
    }

    /**
     * @Route("/new", name="gd_carte_new", methods="GET|POST")
     */
    public function new(Request $request, LieuRepository $repository)
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', "Lieu (".$lieu.') créé.');

            return $this->redirectToRoute('gd_carte');
        }

        return $this->render('Gendarmerie/Lieu/new.html.twig', [
            'lieu' => $lieu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gd_carte_edit", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function edit(Lieu $lieu, Request $request, LieuRepository $repository)
    {
        if ($lieu->getUnits()->indexOf($this->getUser()->getGendarme()->getUnit()) === FALSE)
            throw $this->createAccessDeniedException("Ce lieu ne concerne pas votre unité.");
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', "Lieu (".$lieu.') modifié.');

            return $this->redirectToRoute('gd_carte_show', ['id' => $lieu->getId()]);
        }

        return $this->render('Gendarmerie/Lieu/edit.html.twig', [
            'lieu' => $lieu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_carte_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, Lieu $lieu)
    {
        if ($lieu->getUnits()->indexOf($this->getUser()->getGendarme()->getUnit()) === FALSE)
            throw $this->createAccessDeniedException("Ce lieu ne concerne pas votre unité.");
        if ($this->isCsrfTokenValid('delete'.$lieu->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($lieu);
            $em->flush();
            $this->addFlash('info', "Lieu ".$lieu.' supprimé.');
        }

        return $this->redirectToRoute('gd_carte');
    }

    /**
     * @Route("/{id}", name="gd_carte_show", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function show(Lieu $lieu, Request $request)
    {
        if ($lieu->getUnits()->indexOf($this->getUser()->getGendarme()->getUnit()) === FALSE)
            throw $this->createAccessDeniedException("Ce lieu ne concerne pas votre unité.");

        return $this->render('Gendarmerie/Lieu/show.html.twig', [
            'lieu' => $lieu,
        ]);
    }
}
