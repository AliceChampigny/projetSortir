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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('duree')
            ->add('dateLimiteInscription', null, ['html5' => true, 'widget' => 'single_text'])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')
//            ->add('image', FileType::class, [
//            'label' => 'Image (JPG, PNG, GIF)',
//            'mapped' => false,
//        ]);
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
