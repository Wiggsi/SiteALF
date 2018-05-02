<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSubscriber implements EventSubscriberInterface {
    private $token;
    private $session;

    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->token = $tokenStorage->getToken();
        $this->session = $container->get('session');
    }

    public static function getSubscribedEvents()
    {
        return [
            'security.interactive_login' => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->token != NULL) {
            $user = $this->token->getUser();
            $this->session->getFlashBag()
                ->add('info', 'Bonjour '.$user.' ☺<br/><em>(Dernière activité : '.$user->getLastActivity()->format('d/m/y H:i').').</em>');
        }
    }
}
