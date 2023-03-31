<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
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
        return $this->render('participant/modifier.html.twig',compact('participantForm'));
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
        dump($participant);
        return $this->render('participant/afficherprofil.html.twig',compact('participant'));
}
}
