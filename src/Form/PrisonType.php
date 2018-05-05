<?php

namespace App\Form;

use App\Entity\Prison;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PrisonType extends AbstractType {
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateTimeType::class, array(
                'label'       => "Début",
                'date_widget' => 'single_text',
                'time_widget' => "single_text",
            ))
            ->add('endDate', DateTimeType::class, array(
                'label'       => "Fin",
                'date_widget' => 'single_text',
                'time_widget' => "single_text",
            ))
            ->add('comment', TextareaType::class, array(
                'label'    => 'Commentaires',
                'attr'     => array(
                    'row'          => 10,
                    'autocomplete' => 'off',
                ),
                'required' => FALSE,
            ))
            ->add('validation', ChoiceType::class, array(
                'choices' => array(
                    'Jamais'  => 'Jamais',
                    '24h'     => '24h',
                    '48h'     => '48h',
                    '72h'     => '72h',
                    'Semaine' => 'Semaine',
                ),
                'label'   => 'Contrôle judiciaire',
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer la fiche"));

        if ($this->user->isGendarme() or $this->user->isMagistrat()) {
            $builder
                ->add('criminel', EntityType::class, array(
                    'class'         => 'App:Criminel',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->leftJoin('c.fichePrison', 'p')
                            ->where('p IS NULL')
                            ->orWhere('p IS NOT NULL and p.type != :ended and p.type != :condamne')
                            ->setParameter('ended', 'Terminé')
                            ->setParameter('condamne', 'Condamné')
                            ->orderBy('c.name', 'ASC');
                    },
                    'label'         => '*Individu',
                    'multiple'      => FALSE,
                ))
                ->add('type', ChoiceType::class, array(
                    'choices' => array(
                        'GAV'                   => 'GAV',
                        'Bracelet électronique' => 'Bracelet électronique',
                        'Prison'                => 'Prison',
                        'Autre'                 => 'Autre',
                    ),
                    'label'   => '*Type',
                ))
                ->add('enAttente', CheckboxType::class, array(
                    'label'    => 'En attente de jugement',
                    'required' => FALSE,
                ));
        }

        if ($this->user->isGendarme()) {
            $builder->add('PV', EntityType::class, array(
                'class'         => 'App:PV',
                'query_builder' => function (EntityRepository $er) {
                    return $er->findNonTermines($this->user, FALSE);
                },
                'label'         => 'PV',
                'multiple'      => FALSE,
                'required'      => FALSE,
            ));
        }
        else if ($this->user->isMagistrat()) {
            $builder->add('PV', EntityType::class, array(
                'class'         => 'App:PV',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pv')
                        ->where('pv.status = :termine')->setParameter('termine', 'Terminé')
                        ->orWhere('pv.status = :encours')->setParameter('encours', "En cours de jugement")
                        ->orderBy('pv.updatedDate', 'DESC');
                },
                'label'         => 'PV',
                'multiple'      => FALSE,
                'required'      => FALSE,
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => Prison::class,
                               ]);
    }
}
