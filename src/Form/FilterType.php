<?php

namespace App\Form;

use App\Entity\Campus;
use App\modeles\Filter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add("campus", EntityType::class, [
                "required" => false,
                "class" => Campus::class,
                "choice_label" => "nom"
            ])
            ->add('keyWord',TextType::class, ['label'=>'Le nom de la sortie contient :  ', 'required' => false])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Entre',
                'date_label' => 'dateDebut',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Et',
                'date_label' => 'dateFin',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('organisateurSorties', CheckboxType::class, [
                'label'    => "Sorties dont je suis l'organisateur/trice",
                'required' => false,
                'data' => true,
            ])
            ->add('inscritSorties', CheckboxType::class, [
                'label'    => "Sorties auxquelles je suis inscrit/e ",
                'required' => false,
                'data' => true,
            ])
            ->add('nonInscritSorties', CheckboxType::class, [
                'label'    => "Sorties auxquelles je ne suis pas inscrit/e ",
                'required' => false,
                'data' => true,

            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'label'    => "Sorties passées ",
                'required' => false,

            ])
            ->add("Rechercher", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Filter::class
        ]);
    }
}