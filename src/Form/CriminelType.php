<?php

namespace App\Form;

use App\Entity\Criminel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriminelType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => '*Nom',
                'attr'  => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('firstName', TextType::class, array(
                'label' => '*Prénom',
                'attr'  => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('dangerous', RangeType::class, array(
                'label' => '*Dangerosité',
                'attr'  => array(
                    'min'              => 0,
                    'max'              => 10,
                    'data-rangeslider' => TRUE,
                ),
            ))
            ->add('birthdate', DateType::class, array(
                "label"    => "Date de naissance",
                'widget'   => 'single_text',
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('tel', TextType::class, array(
                'label'    => 'Téléphone',
                'required' => FALSE,
                'attr'     => array(
                    'maxlength'    => 10,
                    'autocomplete' => 'off',
                ),
            ))
            ->add('wanted', CheckboxType::class, array(
                'label'    => 'Recherché',
                'required' => FALSE,
            ))
            ->add('commentaires', TextareaType::class, array(
                'label'    => 'Commentaires',
                'trim'     => FALSE,
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('ADNCode', IntegerType::class, array(
                'label'    => 'Code ADN',
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('photoCode', IntegerType::class, array(
                'label'    => 'Reconnaissance faciale',
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer"));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => Criminel::class,
                               ]);
    }
}
