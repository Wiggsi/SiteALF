<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/Gendarmerie/FVV")
 */
class FVVController extends Controller {
    /**
     * @Route("", name="gd_fvv", methods="GET")
     */
    public function index(VehiculeRepository $vehiculeRepository)
    {
        $vehicules = $vehiculeRepository->findBy(['retrouve' => FALSE], ['date' => 'DESC']);

        return $this->render('FVV/index.html.twig', ['vehicules' => $vehicules, 'historique' => FALSE]);
    }

    /**
     * @Route("/Historique", name="gd_fvv_historique", methods="GET")
     */
    public function historique(VehiculeRepository $vehiculeRepository)
    {
        $vehicules = $vehiculeRepository->findBy(['retrouve' => TRUE], ['date' => 'DESC']);

        return $this->render('FVV/index.html.twig', ['vehicules' => $vehicules, 'historique' => TRUE]);
    }

    /**
     * @Route("/new", name="gd_fvv_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $vehicule = new Vehicule();
        $vehicule->setAuthor($this->getUser()->getGendarme())->setRetrouve(FALSE);
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($vehicule->getPlaque() == NULL) $vehicule->setPlaque('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($vehicule);
            $em->flush();
            $this->addFlash('info', "Véhicule ".$vehicule." défini comme volé.");

            return $this->redirectToRoute('gd_fvv_show', ['id' => $vehicule->getId()]);
        }

        return $this->render('FVV/new.html.twig', [
            'vehicule' => $vehicule,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_fvv_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(Vehicule $vehicule): Response
    {
        return $this->render('FVV/show.html.twig', ['vehicule' => $vehicule]);
    }

    /**
     * @Route("/{id}/retrouve", name="gd_fvv_retrouve", methods="GET", requirements={"id"="\d+"})
     */
    public function retrouve(Vehicule $vehicule): Response
    {
        $vehicule->setRetrouve(TRUE);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('info', "Le véhicule volé a été déclaré retrouvé.");

        return $this->redirectToRoute('gd_fvv');
    }

    /**
     * @Route("/{id}/edit", name="gd_fvv_edit", methods="GET|POST",
     *                      requirements={"id"="\d+"})
     */
    public function edit(Request $request, Vehicule $vehicule)
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Le véhicule volé a été modifié.");

            return $this->redirectToRoute('gd_fvv_show', ['id' => $vehicule->getId()]);
        }

        return $this->render('FVV/edit.html.twig', [
            'vehicule' => $vehicule,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_fvv_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, Vehicule $vehicule)
    {
        if (!$this->getUser()->getGendarme()->isOfficier())
            throw $this->createAccessDeniedException('Seul un officier peut supprimer un véhicule du FVV.');

        if ($this->isCsrfTokenValid('delete'.$vehicule->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vehicule);
            $em->flush();
            $this->addFlash('info', "Le véhicule ".$vehicule." n'est plus enregistré comme volé.");
        }

        return $this->redirectToRoute('gd_fvv');
    }
}
