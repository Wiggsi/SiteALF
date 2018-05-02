<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'    => '*Nom',
                'required' => FALSE,
            ))
            ->add('units', EntityType::class, array(
                'class'    => 'App:Unit',
                'multiple' => TRUE,
                'label'    => '*Unités',
            ))
            ->add('x', NumberType::class, array(
                'label'    => 'Longitude',
                'required' => FALSE,
                'scale'    => 2,
                'attr'     => array(
                    'step'      => "0.001",
                    'maxlength' => 5,
                ),
            ))
            ->add('y', NumberType::class, array(
                'label'    => 'Latitude',
                'required' => FALSE,
                'scale'    => 2,
                'attr'     => array(
                    'step'      => 0.001,
                    'maxlength' => 5,
                ),
            ))
            ->add('icon', ChoiceType::class, array(
                'choices' => array(
                    'Point'        => 'point',
                    'Hangar'       => 'hangar',
                    'À surveiller' => "warning",
                    'Radar'        => "radar",
                ),
                'label'   => '*Icône',
            ))
            ->add('content', TextareaType::class, array(
                'label'    => 'Description',
                'required' => FALSE,
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => Lieu::class,
                               ]);
    }
}
