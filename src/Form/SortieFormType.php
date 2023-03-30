<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use function Sodium\add;

class SortieFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void{

        $builder
            ->add('nom')
            ->add('dateHeureDebut', DateTimeType::class,['html5' => true, 'widget' => 'single_text','required'=>false])
            ->add('duree')
            ->add('dateLimiteInscription', DateTimeType::class,['html5' => true, 'widget' => 'single_text','required'=>false])
            ->add('nbInscriptionsMax')
            ->add('infosSortie',TextareaType::class)
            ->add('ville',EntityType::class,[
                'mapped'=>false,
                'class'=>Ville::class,
                'choice_label'=>'nom',
                'placeholder'=>'Selectionnez la ville'])
            ->add('lieu', ChoiceType::class,[
                    'placeholder'=>'Lieu [choisissez d\'abord une région]'
                    ]
            );

        $formModifier = function (
            FormInterface $form,
            Ville $ville = null){

            $lieux = ($ville === null)? [] : $ville->getLieux();
            dump($lieux);
            $form
                ->add('lieu',EntityType::class,[
                    'class'=>Lieu::class,
                    'choices'=>$lieux,
                    'choice_label'=>'nom',
                    'placeholder'=>'Lieu [choisissez d\'abord une ville]',
                    'label'=>'Lieu',

                ]);
        };

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $formEvent) use($formModifier){
                $ville = $formEvent->getForm()->getData();
                $formModifier($formEvent->getForm()->getParent(),$ville);
            }
        );

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
