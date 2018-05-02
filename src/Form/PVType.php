<?php

namespace App\Form;

use App\Entity\Gendarme;
use App\Entity\PV;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PVType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('OPJ', EntityType::class, array(
                'class'         => Gendarme::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->where('g.opj = TRUE')
                        ->orderBy('g.grade', 'DESC');
                },
                'label'         => '*OPJ référent',
            ))
            ->add('status', ChoiceType::class, array(
                'choices' => array(
                    'Gendarmerie' => array(
                        'En cours'   => 'En cours',
                        'À modifier' => 'À modifier',
                    ),
                    'Tribunal'    => array(
                        'Transféré' => 'Transféré',
                        'Terminé'   => 'Terminé',
                    ),
                    'Autre'       => array(
                        'Autre' => 'Autre',
                    ),
                ),
                'label'   => '*Statut',
            ))
            ->add('importance', RangeType::class, array(
                'attr'  => array(
                    'min'  => 1,
                    'max'  => 5,
                    'step' => 1,
                ),
                "label" => "*Niveau d'importance",
            ))
            ->add('resume', TextType::class, array(
                'label' => '*Résumé',
            ))
            ->add('numero', TextType::class, array(
                'label' => '*Numéro',
            ))
            ->add('visibility', ChoiceType::class, array(
                'choices' => array(
                    'Tous'  => 'Tous',
                    'Unité' => 'Unité',
                    'Perso' => 'Perso',
                ),
                'label'   => '*Visibilité',
            ))
            ->add('content', TextareaType::class, array(
                'label'    => '*Contenu',
                'trim'     => FALSE,
                'required' => FALSE,
            ))
            ->add('criminels', EntityType::class, array(
                'class'         => 'App:Criminel',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'multiple'      => TRUE,
                'label'         => 'Individus impliqués',
                'required'      => FALSE,
                'attr'          => array(
                    'size' => '10',
                ),
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer le PV"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => PV::class,
                               ]);
    }
}
