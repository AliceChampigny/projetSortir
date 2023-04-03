<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/participant',
    name:'participant' )]
class ParticipantController extends AbstractController{
//    #[Route(
//        '',
//        name: '_accueilcnte')]
//    public function accueilcnte(
//
//    ): Response{
//        return $this->redirectToRoute('main_accueil');
//
//    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/profil/modifier/{id}',
        name:'_modifierprofil'
    )]
    public function modifierProfil(
        EntityManagerInterface $entityManager,
        Request $request,
        Participant $id,
        UserPasswordHasherInterface $userPasswordHasher

    ) : Response{

        $participantForm = $this->createForm(ParticipantType::class,$id);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            try{
                $id->setPassword(
                    $userPasswordHasher->hashPassword(
                    $id,
                    $participantForm->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($id);
                $entityManager->flush();
                $this->redirectToRoute('sortie_liste');
                $this->addFlash('success','Votre profil a bien été modifié');
                return $this->redirectToRoute('sortie_liste');
            }catch (\Exception $exception){
                $this->addFlash('danger','La modification n\'a pas été effectué'.$exception->getMessage());
                return $this->redirectToRoute('participant_modifierprofil',['id'=>$id->getId()]);
            }

        }
        return $this->render('participant/modifier.html.twig',compact('participantForm', ));
    }
//----------------------------------------------------------------------------------------------------------------------
#[Route(
    '/profil/{id}',
    name: '_afficherprofil')]
public function afficherProfil(
    Participant $id,
    ParticipantRepository $participantRepository
) : Response{

        $participant = $participantRepository->findOneBy(['id'=>$id->getId()]);
        return $this->render('participant/afficherprofil.html.twig',compact('participant'));
}
//----------------------------------------------------------------------------------------------------------------------

    #[Route(
        '/admin',
        name: '_admin')]
    public function admin(

    ) : Response{

        return $this->render('participant/admin.html.twig');
    }
//----------------------------------------------------------------------------------------------------------------------


    #[Route(
        '/admin/gestionutilisateur',
        name: '_adminGestionUtilisateur')]
    public function adminGestionUtilisateur(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
        {
            $user = new Participant();
            $user -> setActif(true);
            $user -> setAdministrateur(false);
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try{
                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $entityManager->persist($user);
                    $entityManager->flush();
                    // do anything else you need here, like send an email
                    $this->addFlash('danger','L\'inscription a bien été enregistrée');
                    return $this->redirectToRoute('participant_admin');
                }catch (\Exception $exception){
                    $this->addFlash('danger','L\'inscription n\'a pas été effectuée'.$exception->getMessage());
                    return $this->redirectToRoute('participant_adminGestionUtilisateur');
                }


            }

            return $this->render('participant/adminGestionUtilisateur.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }

//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/admin/gestionsortie',
        name: '_adminGestionSortie')]
    public function adminGestionSortie(

    ) : Response{


        return $this->render('participant/adminGestionSortie.html.twig');
    }

}
