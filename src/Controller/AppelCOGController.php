<?php

namespace App\Controller;

use App\Entity\AppelCOG;
use App\Form\AppelCOGType;
use App\Repository\AppelCOGRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/Gendarmerie/COG")
 */
class AppelCOGController extends Controller
{
    /**
     * @Route("", name="gd_cog", methods="GET")
     */
    public function index(AppelCOGRepository $appelCOGRepository)
    {
        $appelCOGs = $appelCOGRepository->findLatest(2);

        return $this->render('COG/index.html.twig', ['appelCOGs' => $appelCOGs]);
    }

    /**
     * @Route("/new", name="gd_cog_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $appelCOG = new AppelCOG();
        $appelCOG->setAuthor($this->getUser()->getGendarme());
        $form = $this->createForm(AppelCOGType::class, $appelCOG);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($appelCOG);
            $em->flush();
            $this->addFlash('info', "L'appel COG a été enregistré.");

            return $this->redirectToRoute('gd_cog_show', ['id' => $appelCOG->getId()]);
        }

        return $this->render('COG/new.html.twig', [
            'appelCOG' => $appelCOG,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_cog_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(AppelCOG $appelCOG): Response
    {
        return $this->render('COG/show.html.twig', ['appelCOG' => $appelCOG]);
    }

    /**
     * @Route("/{id}/edit", name="gd_cog_edit", methods="GET|POST",
     *                      requirements={"id"="\d+"})
     */
    public function edit(Request $request, AppelCOG $appelCOG)
    {
        $form = $this->createForm(AppelCOGType::class, $appelCOG);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "L'appel COG a été modifié.");

            return $this->redirectToRoute('gd_cog_show', ['id' => $appelCOG->getId()]);
        }

        return $this->render('COG/edit.html.twig', [
            'appelCOG' => $appelCOG,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gd_cog_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, AppelCOG $appelCOG)
    {
        if ($appelCOG->getAuthor() != $this->getUser()->getGendarme() and
            !$this->getUser()->getGendarme()->isOfficier())
        {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires à la suppression.");
        }

        if ($this->isCsrfTokenValid('delete'.$appelCOG->getId(), $request->request->get('_token')))
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($appelCOG);
            $em->flush();
            $this->addFlash('info', "L'appel COG a été supprimé.");
        }

        return $this->redirectToRoute('gd_cog');
    }
}
