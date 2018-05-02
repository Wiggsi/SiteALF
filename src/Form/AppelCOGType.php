<?php

namespace App\Form;

use App\Entity\AppelCOG;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppelCOGType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, array(
                'label'    => 'Prénom',
                'required' => FALSE,
            ))
            ->add('name', TextType::class, array(
                'label'    => '*Nom',
                'required' => FALSE,
                'attr'     => array(
                    'maxlength' => 10,
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label'    => '*Contenu',
                'required' => FALSE,
            ))
            ->add('tel', TextType::class, array(
                'label' => '*N° de téléphone',
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AppelCOG::class,
        ]);
    }
}
