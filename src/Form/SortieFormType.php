<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'feed-form',
                    'placeholder' => "Nom de l'évènement"
                )
            ))
            ->add('dateHeureDebut', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('duree', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'feed-form',
                    'placeholder' => "Durée de l'évènement (en minutes)"
                )
            ))
            ->add('dateLimiteInscription', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('nbInscriptionsMax', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'feed-form',
                    'placeholder' => 'Nombre de participants max'
                )
            ))
            ->add('infosSortie', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'feed-form',
                    'placeholder' => "Descrption de l'évènement"
                )
            ))
            ->add('lieu', EntityType::class,[
                'class' => Lieu::class,
                    'choice_label' => 'nom',
                    ]
            );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
