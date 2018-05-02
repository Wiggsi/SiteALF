<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                => ['all' => TRUE],
    Symfony\Bundle\TwigBundle\TwigBundle::class                          => ['all' => TRUE],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class            => ['all' => TRUE],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                  => ['all' => TRUE],
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class       => ['all' => TRUE],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                 => ['all' => TRUE],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class     => ['all' => TRUE],
    Symfony\Bundle\MonologBundle\MonologBundle::class                    => ['all' => TRUE],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => TRUE],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class            => ['dev' => TRUE, 'test' => TRUE],
    Symfony\Bundle\DebugBundle\DebugBundle::class                        => ['dev' => TRUE, 'test' => TRUE],
    Symfony\Bundle\MakerBundle\MakerBundle::class                        => ['dev' => TRUE],
    Symfony\Bundle\WebServerBundle\WebServerBundle::class                => ['dev' => TRUE],
    EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle::class               => ['all' => TRUE],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class         => ['dev' => TRUE, 'test' => TRUE],
];
