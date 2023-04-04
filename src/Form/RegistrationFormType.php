<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;
use function Sodium\add;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, array(
                'required' => true,
                'label' => ' ',
                'attr' => array(
                    'class' => 'input',
                    'placeholder' => 'Nom'
                )
            ))
            ->add('prenom', TextType::class, array(
                'required' => true,
                'label' => ' ',
                'attr' => array(
                    'class' => 'input',
                    'placeholder' => 'Prénom'
                )
            ))
            ->add('pseudo', TextType::class, array(
                'required' => true,
                'label' => ' ',
                'attr' => array(
                    'class' => 'input',
                    'placeholder' => 'Pseudo'
                )
            ))
            ->add('email', TextType::class, array(
                'required' => true,
                'label' => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => 'E-mail'
                )
            ))
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
            ->add('telephone', TextType::class, array(
                'required' => true,
                'label' => ' ',
                'attr' => array(
                    'class' => 'input2',
                    'placeholder' => 'Telephone'
                )
            ))
            ->add('campus', EntityType::class,
                [
                    'label' => ' ',
                    'attr' => array(
                        'class' => 'input3',

                    ),
                    'class' => Campus::class,
                    'choice_label' => 'nom',

                ]
            )



            ->add('plainPassword', RepeatedType::class, [
        'type' => PasswordType::class,
        // instead of being set onto the object directly,
        // this is read and encoded in the controller
        'mapped' => false,
        'attr' => ['autocomplete' => 'new-password'],
        'constraints' => [
            new NotBlank([
                'message' => 'Veuillez entrer votre mot de passe',
            ]),
            new Length([
                'min' => 6,
                'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                // max length allowed by Symfony for security reasons
                'max' => 4096,
            ]),
        ],
        'invalid_message' => 'Les mots de passe doivent correspondre.',
        'options' => ['attr' => ['class' => 'feed-form']],
        'required' => true,
        'first_options' => ['label' => ' ', 'attr' => array('placeholder' => 'Mot de passe', 'class' => 'input2')],
        'second_options' => ['label' => ' ', 'attr' => array('placeholder' => 'Confirmation du mot de passe', 'class' => 'input2')]
    ]);
    }

//----------------------------------------------------------------------------------------------------------------------
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
