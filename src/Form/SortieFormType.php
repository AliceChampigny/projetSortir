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
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

            ->add('nom', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => "Nom de l'évènement"
                )
            ))
            ->add('dateHeureDebut', null, ['attr'=>array(
                'class'=>'input2',
                'label'=>' '
            ), 'html5' => true, 'widget' => 'single_text'])
            ->add('duree', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => "Durée de l'évènement (en minutes)"
                )
            ))
            ->add('dateLimiteInscription', null, ['attr'=>array(
                'class'=>'input2'
            ) ,'html5' => true, 'widget' => 'single_text'])

            ->add('nbInscriptionsMax', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => 'Nombre de participants max'
                )
            ))
            ->add('infosSortie', TextType::class, array(
                'required' => true,
                'label'  => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => "Description de l'évènement"
                )
            ))
             ->add('ville',EntityType::class,[
                'mapped'=>false,
                'class'=>Ville::class,
                'choice_label'=>'nom',
                'placeholder'=>'Selectionnez la ville',
                'attr' => array(
                    'class'=>'input3'
                )
             ])

            ->add('lieu', EntityType::class,[
                'class'=>Lieu::class,

               'choice_label'=>'nom',
                'placeholder'=>'Lieu [choisissez d\'abord une ville]',
                'attr'=>array(
                    'class'=>'input3',

                )
                ]);

        $formModifier = function (
            FormInterface $form,
            Ville $ville = null){

            $lieux = ($ville === null)? [] : $ville->getLieux();
            $form
                ->add('lieu',EntityType::class,[
                    'class'=>Lieu::class,
                    'choices'=>$lieux,
                    'choice_label'=>'nom',
                    'placeholder'=>'Sélectionnez votre lieu',
                    'label'=>'Lieu',
                    'attr'=> array(
                    'class'=>'input3')

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
