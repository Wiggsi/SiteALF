<?php

namespace App\Controller;

use App\Entity\Infraction;
use App\Repository\InfractionRepository;
use App\Repository\MagistratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends Controller {
    /**
     * @Route("", name="homepage")
     */
    public function index()
    {
        return $this->render('Site/index.html.twig');
    }

    /**
     * @Route("/Infractions", name="infractions")
     */
    public function infractions(InfractionRepository $infractionRepository, MagistratRepository $magistratRepository)
    {
//        $infractions = $infractionRepository->findBy([], ['type' => 'DESC']);
        $magistrats = $magistratRepository->findAll();

        return $this->render('Site/infractions.html.twig', ['magistrats' => $magistrats]);
    }

    /**
     * @Route("/Infractions/new", name="infraction_new", methods="POST")
     */
    public function addInfraction(Request $request)
    {
        if (!$this->getUser()->isGendarme() and !$this->getUser()->isMagistrat()) {
            return $this->createAccessDeniedException("Vous ne pouvez pas ajouter d'infraction à la base de données.");
        }
        $type = $request->get('_type');
        $name = $request->get('_name');
        $definition = $request->get('_definition');
        $content = $request->get('_content');
        $infraction = new Infraction();
        $infraction->setName($name)->setType($type)->setContent($content)->setDefinition($definition);
        $em = $this->getDoctrine()->getManager();
        $em->persist($infraction);
        $em->flush();

        $this->addFlash('info', "L'infraction ".$infraction." a bien été ajoutée.");

        return $this->redirectToRoute('infraction');
    }
}