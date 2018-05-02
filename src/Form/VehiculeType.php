<?php

namespace App\Form;

use App\Entity\Vehicule;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('propName', TextType::class, array(
                'label' => '*Nom du propriétaire',
            ))
            ->add('propFirstName', TextType::class, array(
                'label' => '*Prénom du propriétaire',
            ))
            ->add('date', DateTimeType::class, array(
                'label'       => 'Date et Heure',
                'date_widget' => 'single_text',
                'time_widget' => "single_text",
            ))
            ->add('type', TextType::class, array(
                'label' => '*Type de véhicule',
                'attr'  => array(
                    'placeholder' => 'Renault R11 Noir',
                ),
            ))
            ->add('plaque', TextType::class, array(
                'label'    => 'Plaque du véhicule',
                'required' => FALSE,
                'attr'     => array(
                    'placeholder' => 'AB-123-CD',
                ),
            ))
            ->add('comment', TextareaType::class, array(
                'label' => 'Commentaires',
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer le véhicule"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => Vehicule::class,
                               ]);
    }
}
