<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('rue',null,[
                'label'=>' ',
                'attr'=>['hidden'=>true]])
            ->add('longitude',null,[
                'label'=>' ',
                'attr'=>['hidden'=>true]])
            ->add('latitude',null,[
                'label'=>' ',
                'attr'=>['hidden'=>true]])
            ->add('ville',EntityType::class,[
                'class'=>Ville::class,
                'choice_label'=>'code_postal',
                'label'=>'Sélectionnez un code postal'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
