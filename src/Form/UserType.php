<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, array(
                'label' => '*Prénom',
            ))
            ->add('name', TextType::class, array(
                'label' => '*Nom',
            ))
            ->add('username', TextType::class, array(
                'label' => "*Nom d'utilisateur : Prénom + Nom",
                'attr'  => array(
                    'placeholder' => 'Prénom Nom',
                ),
            ))
            ->add('birthdate', DateType::class, array(
                'label'  => '*Date de naissance',
                'widget' => 'single_text',
            ))
            ->add('email', EmailType::class, array(
                'label' => '*E-mail',
            ))
            ->add('password', RepeatedType::class, array(
                'type'           => PasswordType::class,
                'first_options'  => array('label' => '*Mot de Passe'),
                'second_options' => array('label' => '*Répétez votre mot de passe'),
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                                   'data_class' => User::class,
                               ));
    }
}