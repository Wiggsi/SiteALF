<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostType extends AbstractType {
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => '*Titre',
                'attr'  => array(
                    'placeholder' => 'Titre du post',
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label'    => '*Contenu',
                'required' => FALSE,
            ))
            ->add('public', CheckboxType::class, array(
                'label'    => 'Communiqué à tous les services publics',
                'required' => FALSE,
                'mapped'   => FALSE,
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer le Post"));

        if ($this->user->isGendarme()) {
            $builder
                ->add('unit', EntityType::class, array(
                    'label' => '*Unité concerné',
                    'class' => 'App:Unit',
                ))
                ->add('section', EntityType::class, array(
                    'label'    => 'Section concerné',
                    'class'    => 'App:Section',
                    'required' => FALSE,
                ))
                ->add('category', ChoiceType::class, array(
                    'label'   => '*Catégorie',
                    'choices' => array(
                        'Information' => 'Information',
                        'Communiqué'  => 'Communiqué',
                        'Formation'   => 'Formation',
                        'Autre'       => 'Autre',
                    ),
                ));
        }
        else if ($this->user->isGardien()) {
            $builder
                ->add('category', ChoiceType::class, array(
                    'label'   => '*Catégorie',
                    'choices' => array(
                        "Compte-Rendu Professionnel (CRP)" => 'CRP',
                        "Compte-Rendu d'Incident (CRI)"    => 'CRI',
                        'Communiqué'                       => 'Communiqué',
                        'Formation'                        => 'Formation',
                        'Autre'                            => 'Autre',
                    ),
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => Post::class,
                               ]);
    }
}
