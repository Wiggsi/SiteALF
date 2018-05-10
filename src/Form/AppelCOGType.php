<?php

namespace App\Form;

use App\Entity\AppelCOG;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppelCOGType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, array(
                'label'    => 'Prénom',
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('name', TextType::class, array(
                'label'    => '*Nom',
                'required' => FALSE,
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
            ))
            ->add('content', TextareaType::class, array(
                'label'    => '*Contenu',
                'attr'     => array(
                    'autocomplete' => 'off',
                ),
                'required' => FALSE,
            ))
            ->add('tel', TelType::class, array(
                'label' => '*N° de téléphone',
                'attr'  => array(
                    'autocomplete' => 'off',
                    'pattern'      => '^(0)[1-9][0-9]{8}$',
                    'title'        => 'N° de téléphone valide demandé',
                ),
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
