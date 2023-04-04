<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom',TextType::class,['label'=>' ', 'attr'=> array('class'=>'input2')])
            ->add('prenom',TextType::class,['label'=>' ', 'attr'=> array('class'=>'input2')])
            ->add('pseudo',TextType::class,['label'=>' ', 'attr'=> array('class'=>'input2')])
            ->add('email',TextType::class,['label'=>' ', 'attr'=> array('class'=>'input2')])
            ->add('telephone',TextType::class,['label'=>' ', 'attr'=> array('class'=>'input2')])
            ->add('imageFile',VichImageType::class,[
                'label'=> ' ',
                'attr'=> ['class'=>'input3',
                ],
                'mapped'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png']
                    ]),

                ]
            ])
            ->add('plainPassword',RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être renseigné',
                'options' => ['attr' => ['class' => 'input2', 'placeholder'=>'Mot de passe']],
                'required' => true,

                'first_options'  => ['label' => 'Mot de passe '],
                'second_options' => ['label' => 'Confirmation du mot de passe ']])
            ->add('campus',EntityType::class,['class'=>Campus::class,'choice_label'=>'nom','disabled'=>true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
