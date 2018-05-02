<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Post;
use App\Entity\Unit;
use App\Form\PostType;
use App\Repository\MessageRepository;
use App\Repository\PostRepository;
use App\Repository\UnitRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/Prison/Messages")
 */
class APPostsController extends Controller {
    /**
     * @Route("", name="ap_post", methods="GET")
     */
    public function index(PostRepository $postRepository, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $posts = $postRepository->findByUser($this->getUser(), 30, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'ap_post',
            'pages_count'  => ceil(count($posts) / 30),
            'route_params' => array(),
            'total'        => count($posts),
        );

        return $this->render('Prison/Post/index.html.twig', [
            'posts'      => $posts,
            'titre'      => 'Messages',
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="ap_post_new", methods="GET|POST", requirements={"id"="\d+"})
     */
    public function new(Request $request, UnitRepository $unitRepository): Response
    {
        $post = new Post();
        $post->setAuthor($this->getUser());
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()['public']) $post->setUnit(NULL);
            $em = $this->getDoctrine()->getManager();
            $post->setUnit($unitRepository->findOneBy(['name' => 'Prison']));
            $em->persist($post);
            $em->flush();
            $this->addFlash('info', 'Post '.$post.' envoyé.');

            return $this->redirectToRoute('ap_post_show', ['id' => $post->getId()]);
        }

        return $this->render('Prison/Post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ap_post_show", methods="GET", requirements={"id"="\d+"})
     */
    public function show(Post $post, MessageRepository $messageRepository, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $query = $messageRepository->createQueryBuilder('m')
            ->join('m.author', 'a')
            ->where('m.post = :post')->setParameter('post', $post)
            ->orderBy('m.createdDate', 'DESC')
            ->setFirstResult(($page - 1) * 16)
            ->setMaxResults(16);
        $messages = new Paginator($query);
        $pagination = array(
            'page'         => $page,
            'route'        => 'ap_post_show',
            'pages_count'  => ceil(count($messages) / 16),
            'route_params' => array('id' => $post->getId()),
            'total'        => count($messages),
        );

        return $this->render('Prison/Post/show.html.twig', [
            'post'       => $post,
            'messages'   => $messages,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ap_post_edit", methods="GET|POST",
     *                      requirements={"id"="\d+"})
     */
    public function edit(Request $request, Post $post)
    {
        if (!$this->getUser()->getGardien()->isOfficier() and $this->getUser() != $post->getAuthor()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires pour modifier ce post.");
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()['public']) $post->setUnit(NULL);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'Post '.$post.' modifié.');

            return $this->redirectToRoute('ap_post_show', ['id' => $post->getId()]);
        }

        return $this->render('Prison/Post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ap_post_delete", methods="DELETE", requirements={"id"="\d+"})
     */
    public function delete(Request $request, Post $post)
    {
        if ($this->getUser() != $post->getAuthor() and !$this->getUser()->getGardien()->isOfficier()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires pour supprimer ce post.");
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
            $this->addFlash('info', 'Post '.$post.' supprimé.');
        }
        else {
            $this->addFlash('warning', 'Erreur.');
        }

        return $this->redirectToRoute('ap_post');
    }

    /**
     * @Route("/{category}", name="ap_post_category", methods="GET")
     */
    public function postsCategory($category, PostRepository $rep, Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $posts = $rep->findByCategory($this->getUser(), $category, 30, $page);
        $pagination = array(
            'page'         => $page,
            'route'        => 'ap_post_category',
            'pages_count'  => ceil(count($posts) / 30),
            'route_params' => array(),
            'total'        => count($posts),
        );

        return $this->render('Prison/Post/index.html.twig', [
            'posts'      => $posts,
            'titre'      => $category,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{idPost}/newMessage", name="ap_post_message_new", methods="POST", requirements={"idPost"="\d+"})
     */
    public function newMessage($idPost, Request $request, PostRepository $rep)
    {
        $message = new Message();
        $content = $request->get('_content');
        if ($content != '' and $content != NULL) {
            $post = $rep->findOneBy(['id' => $idPost]);
            $message->setAuthor($this->getUser());
            $message->setPost($post);
            $message->setContent($content);
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash('info', 'Message pour le post ('.$post.') envoyé.');

            return $this->redirectToRoute('ap_post_show', ['id' => $idPost]);
        }

        $this->addFlash('danger', "Erreur lors de l'envoi du message");

        return $this->redirectToRoute('ap_post_show', ['id' => $idPost]);
    }

    /**
     * @Route("/{idPost}/messages/{id}", name="ap_post_message_delete", methods="DELETE", requirements={"idPost"="\d+",
     *     "id"="\d+"})
     */
    public function deleteMessage(Request $request, Message $message, $idPost)
    {
        if ($this->getUser() != $message->getAuthor() and !$this->getUser()->getGardien()->ifOfficier()) {
            throw $this->createAccessDeniedException("Vous n'avez pas les accréditations nécessaires pour supprimer ce message");
        }

        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();
            $this->addFlash('info', 'Message supprimé.');
        }
        else {
            $this->addFlash('warning', 'Erreur.');
        }

        return $this->redirectToRoute('ap_post_show', ['id' => $idPost]);
    }
}
