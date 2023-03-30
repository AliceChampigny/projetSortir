<?php

namespace App\Form;

use App\Entity\Campus;
use App\modeles\Filter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyWord')
            ->add("campus", EntityType::class, [
                "required" => false,
                "class" => Campus::class,
                "choice_label" => "nom"
            ])
            ->add('dateDebut', DateTimeType::class, [
                'date_label' => 'dateDebut',
                'required' => false,
                ])
            ->add('dateFin', DateTimeType::class, [
                'date_label' => 'dateFin',
                'required' => false,
            ])
            ->add('organisateurSorties', CheckboxType::class, [
                'label'    => "Sorties dont je suis l'organisateur/trice",
                'required' => false,
            ])
            ->add('inscritSorties', CheckboxType::class, [
                'label'    => "Sorties auxquelles je suis inscrit/e ",
                'required' => false,
            ])
            ->add('nonInscritSorties', CheckboxType::class, [
                'label'    => "Sorties auxquelles je ne suis pas inscrit/e ",
                'required' => false,

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
