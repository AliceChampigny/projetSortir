<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('password',RepeatedType::class, [

                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être renseigné',
                'options' => ['attr' => ['class' => 'input2', 'placeholder'=>'Mot de passe']],
                'required' => true,
                'first_options'  => ['label' => ' '],
                'second_options' => ['label' => ' ',]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
