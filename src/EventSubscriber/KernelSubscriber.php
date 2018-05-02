<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class KernelSubscriber implements EventSubscriberInterface {
    private $entityManager;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.terminate' => 'onKernelController',
        ];
    }

    public function onKernelController(PostResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if ($token != NULL) {
            $user = $token->getUser();
            if ($user instanceof User) {
                $user->setLastActivity(new \Datetime());
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                if ($user->getBlocked()) {
                    throw new AccessDeniedException('Votre compte a été bloqué.');
                }
            }
        }

    }
}
