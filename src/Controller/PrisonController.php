<?php

namespace App\Controller;

use App\Entity\Criminel;
use App\Entity\Gardien;
use App\Entity\Prison;
use App\Entity\User;
use App\Form\PrisonType;
use App\Repository\PrisonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/Prison")
 */
class PrisonController extends Controller {
    /**
     * @Route("/Prisonniers", name="prison_list")
     */
    public function prisonniers(PrisonRepository $rep)
    {
        $prisons = $rep->findByType(['Prison', 'Évadé']);

        return $this->render('Prison/list.html.twig', [
            'prisons' => $prisons,
            'titre'   => "En cours",
        ]);
    }

    /**
     * @Route("/Prisonniers/Historique", name="prison_list_ended")
     */
    public function historique(PrisonRepository $rep)
    {
        $prisons = $rep->findByType(['Prison', 'Évadé'], TRUE);

        return $this->render('Prison/list.html.twig', [
            'prisons' => $prisons,
            'titre'   => "Terminés",
        ]);
    }

    /**
     * @Route("/Prisonniers/new/{type}", name="prison_new", defaults={"type": "Autre"})
     */
    public function newIncarceration(Request $request, $type)
    {
        $prison = new Prison();
        $prison->setType($type);
        $prison->setAuthor($this->getUser()->getGendarme());

        if ($type == "GAV") {
            $typeA = "de GAV";
        }
        else if ($type == "Bracelet électronique") {
            $typeA = "de surveillance électronique";
            $prison->setValidation("48h");
        }
        else {
            $typeA = "d'incarcération";
        }
        $form = $this->createForm(PrisonType::class, $prison);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $crim = $prison->getCriminel();
            if (!$crim->isFree()) {
                if ($crim->getFichePrison()->getType() == "Évadé") {
                    $this->addFlash("warning", "La gendarmerie doit déclarer l'individu comme rattrapé.");

                    return $this->redirectToRoute('prison_list');
                }
                else
                    $crim->getFichePrison()->setEnded(TRUE);
                $this->addFlash('info', 'La fiche précédente a été terminée.');
            }
            if ($crim->getWanted()) {
                $this->addFlash('success', "L'individu n'est plus recherché.");
            }
            if ($this->getUser()->isMagistrat()) $prison->setAuthor($prison->getPV()->getAuthor());

            $crim->setFichePrison($prison)->setWanted(FALSE);
            $em = $this->getDoctrine()->getManager();
            $em->persist($prison);
            $em->flush();
            $this->addFlash('info', "Fiche ".$prison." créée.");

            if ($prison->getDuree()->d > 7 and $prison->isPrison())
                $this->addFlash('warning', 'La peine dépasse les 7 jours maximums.');

            return $this->redirectToRoute('prison_show', ['id' => $prison->getId()]);
        }

        return $this->render('Prison/new.html.twig', [
            'prison' => $prison,
            'form'   => $form->createView(),
            'type'   => $typeA,
        ]);
    }

    /**
     * @Route("/Prisonniers/{id}", name="prison_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(Prison $prison)
    {
        return $this->render('Prison/show.html.twig', [
            'prison' => $prison,
        ]);
    }

    /**
     * @Route("/Prisonniers/{id}/edit", name="prison_edit", requirements={"id"="\d+"})
     */
    public function editIncarceration(Request $request, Prison $prison)
    {
        $form = $this->createForm(PrisonType::class, $prison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prison->getCriminel()->setWanted(FALSE);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Fiche ".$prison." modifiée.");

            return $this->redirectToRoute('prison_show', ['id' => $prison->getId()]);
        }

        return $this->render('Prison/edit.html.twig', [
            'prison' => $prison,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/Prisonniers/{id}/Pointage", name="prison_pointage", requirements={"id"="\d+"})
     */
    public function pointage(Request $request, Prison $prison)
    {
        $prison->setValidationDate(new \DateTime());
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('info', "Contrôle effectué.");

        return $this->redirectToRoute('prison_show', ['id' => $prison->getId()]);
    }

    /**
     * @Route("/Prisonniers/{id}/evasion", name="prison_evasion", requirements={"id"="\d+"})
     */
    public function evasionIncarceration(Request $request, Prison $prison)
    {
        $prison->setType("Évadé");
        $prison->getCriminel()->setWanted(TRUE);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('info', $prison->getCriminel().' a été déclaré comme évadé et est désormais recherché.');

        return $this->redirectToRoute('prison_show', ['id' => $prison->getId()]);
    }

    /**
     * @Route("/Prisonniers/{id}/end", name="prison_end", requirements={"id"="\d+"})
     */
    public function endIncarceration(Prison $prison)
    {
        $prison->setEnded(TRUE);
        $prison->getCriminel()->setFichePrison(NULL);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('info', $prison);

        return $this->redirectToRoute('prison_list');
    }

    /**
     * @Route("/Prisonniers/{id}/liberation", name="prison_liberation", requirements={"id"="\d+"})
     */
    public function liberationIncarceration(Prison $prison)
    {
        if (!$this->getUser()->isMagistrat()) throw $this->createAccessDeniedException('Vous devez être un magistrat pour autoriser une libération conditionelle.');
        $prison->setEnded(TRUE);

        $prison2 = new Prison();
        $prison2->setType("Bracelet électronique")->setEnAttente(FALSE)->setStartDate(new \Datetime())
            ->setEndDate(new \Datetime('+7days'))->setValidation('72h')->setAuthor($prison->getAuthor())
            ->setCriminel($prison->getCriminel())
            ->setComment('Libération conditionelle de la fiche<br/><a href="'.$this->generateUrl('prison_show', ['id' => $prison->getId()])
                         .'">'.$prison.'</a><br/>
                        Libération autorisée par '.$this->getUser()->getMagistrat())
            ->setPV($prison->getPV());
        $prison->getCriminel()->setFichePrison($prison2);
        $em = $this->getDoctrine()->getManager();
        $em->persist($prison2);
        $em->flush();
        $this->addFlash('info', $prison->getCriminel().' a été placé en liberté conditionelle.');

        return $this->redirectToRoute('prison_show', ['id' => $prison2->getId()]);
    }

    /**
     * @Route("Prisonniers/{id}/endEvasion", name="prison_evasion_end", requirements={"id"="\d+"})
     */
    public function endEvasion(Prison $prison)
    {
        if (!$this->getUser()->isGendarme())
            throw $this->createAccessDeniedException("Seul un gendarme peut valider la fin d'évasion");

        if (!$prison->getCriminel()->getFichePrison() != $prison)
            throw $this->createNotFoundException("La fiche d'incarcération de l'individu concerné n'est pas la même");

        $prisonNew = new Prison();
        $prisonNew->setCriminel($prison->getCriminel())->setAuthor($this->getUser()->getGendarme())
            ->setType("En attente de jugement")->setPV($prison->getPV())
            ->setStartDate(new \DateTime())->setEndDate(new \DateTime("+7days"))
            ->setComment($prison->getComment()."<br/><em>Fiche créée à cause de la fin de l'évasion : <a href='"
                         .$this->generateUrl("prison_show", ['id' => $prison->getId()])."'>"
                         .$prison."</a>.</em>");
        $prison->setEnded(TRUE);
        $prison->getCriminel()->setWanted(FALSE)->setFichePrison($prisonNew);
        $em = $this->getDoctrine()->getManager();
        $em->persist($prisonNew);
        $em->flush();

        $this->addFlash('info', $prison->getCriminel()." n'est plus évadé et a été remis en prison.");

        return $this->redirectToRoute('prison_show', ['id' => $prisonNew->getId()]);
    }
}
