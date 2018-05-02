<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserController extends Controller {
    /**
     * @Route("/Profil", name="user_homepage")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $formPassword = $this->createFormBuilder()
            ->add('oldPassword', PasswordType::class, array(
                'constraints' => array(
                    new UserPassword(),
                ),
                'attr'        => array(
                    'autocomplete' => 'on',
                ),
                'mapped'      => FALSE,
                'label'       => 'Mot de passe actuel',
            ))->add('newPassword', RepeatedType::class, array(
                'type'            => PasswordType::class,
                'invalid_message' => 'Le mot de passe est différent.',
                'required'        => TRUE,
                'first_options'   => array('label' => 'Nouveau mot de passe', 'attr' => array('autocomplete' => 'on')),
                'second_options'  => array('label' => 'Répéter le nouveau mot de passe', 'attr' => array('autocomplete' => 'on')),
            ))->getForm();
        $formEmail = $this->createFormBuilder()
            ->add('email', EmailType::class, array(
                'label' => 'Nouvel E-mail',
            ))->getForm();
        $formDate = $this->createFormBuilder()
            ->add('date', DateType::class, array(
                'label'  => 'Nouvelle date de naissance',
                'widget' => 'single_text',
            ))->getForm();

        if ($this->getUser()->isGendarme()) {
            $data = $this->getUser()->getGendarme()->getCommentaires();
        }
        else if ($this->getUser()->isMagistrat()) {
            $data = $this->getUser()->getMagistrat()->getCommentaires();
        }
        else {
            $data = '';
        }

        $formCommentaires = $this->createFormBuilder()
            ->add('comment', TextareaType::class, array(
                'label' => 'Commentaires concernant votre métier',
                'data'  => $data,
            ))->getForm();

        $formPassword->handleRequest($request);
        $formEmail->handleRequest($request);
        $formDate->handleRequest($request);
        $formCommentaires->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $newPassword = $passwordEncoder->encodePassword($this->getUser(), $formPassword->getData()['newPassword']);
            $this->getUser()->setPassword($newPassword);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            return $this->redirect($this->generateUrl('user_homepage'));
        }
        else if ($formEmail->isSubmitted() && $formEmail->isValid()) {
            $this->getUser()->setEmail($formEmail->getData()['email']);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre e-mail a été modifié.');

            return $this->redirect($this->generateUrl('user_homepage'));
        }
        else if ($formDate->isSubmitted() && $formDate->isValid()) {
            $this->getUser()->setBirthDate($formDate->getData()['date']);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre date de naissance a été modifiée.');

            return $this->redirect($this->generateUrl('user_homepage'));
        }
        else if ($formCommentaires->isSubmitted() && $formCommentaires->isValid()) {
            if ($this->getUser()->isGendarme()) {
                $this->getUser()->getGendarme()->setCommentaires($formCommentaires->getData()['comment']);
            }
            else if ($this->getUser()->isMagistrat()) {
                $this->getUser()->getMagistrat()->setCommentaires($formCommentaires->getData()['comment']);
            }
            else if ($this->getUser()->isGardien()) {
                $this->getUser()->getGardien()->setCommentaires($formCommentaires->getData()['comment']);

            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Votre description a été modifiée.');

            return $this->redirect($this->generateUrl('user_homepage'));
        }

        return $this->render('User/index.html.twig', array(
            'formPassword'     => $formPassword->createView(),
            'formEmail'        => $formEmail->createView(),
            'formDate'         => $formDate->createView(),
            'formCommentaires' => $formCommentaires->createView(),
        ));
    }

    /**
     * @Route("/27072015/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', "Compte créé : bienvenue ☺ !");

            return $this->redirectToRoute('security_login');
        }

        return $this->render('Security/register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/20081998/cache_clear", name="cache_clear")
     */
    public function cacheClear(KernelInterface $kernel)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $application = new Application($kernel);
        $application->setAutoExit(FALSE);

        $input = new ArrayInput(array('command' => 'cache:clear',
                                      '--env'   => 'prod'));

//        $output = new NullOutput();
        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();

        return new Response($content);
    }
}
