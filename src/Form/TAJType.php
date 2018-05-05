<?php

namespace App\Form;

use App\Entity\TAJ;
use App\Repository\PVRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TAJType extends AbstractType {
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('infractions', CollectionType::class, array(
                'entry_type' => TextType::class,
                'allow_add'  => TRUE,
                'required'   => FALSE,
                'label'      => FALSE,
//                'attr'       => array(
//                    'size' => '10',
//                ),
            ))
            /*->add('infractions', EntityType::class, array(
                'class'    => 'App:Infraction',
                'multiple' => TRUE,
                'label'    => '*Infractions',
                'attr'     => array(
                    'size' => '10',
                ),
            ))
            */
            ->
            add('criminels', EntityType::class, array(
                'class'         => 'App:Criminel',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'multiple'      => TRUE,
                'label'         => '*Criminels',
                'attr'          => array(
                    'size' => '15',
                ),
            ))
            ->add('save', SubmitType::class, array(
                'label' => "Enregistrer le TAJ"));

        if ($this->user->isGendarme()) {
            $builder->add('PV', EntityType::class, array(
                'class'         => 'App:PV',
                'query_builder' => function (PVRepository $er) {
                    return $er->findNonTermines($this->user, FALSE);
                },
                'multiple'      => FALSE,
                'label'         => 'PV',
                'required'      => FALSE,
            ));
        }
        else {
            $builder->add('PV', EntityType::class, array(
                'class'         => 'App:PV',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pv')
                        ->where('pv.status = :termine')->setParameter('termine', 'Terminé')
                        ->orWhere('pv.status = :encours')->setParameter('encours', "En cours de jugement")
                        ->orderBy('pv.updatedDate', 'DESC');
                },
                'multiple'      => FALSE,
                'label'         => '*PV',
                'required'      => TRUE,
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => TAJ::class,
                               ]);
    }
}
