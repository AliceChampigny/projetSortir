<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/participant',
    name:'participant' )]
class ParticipantController extends AbstractController{
    #[Route(
        '',
        name: '_accueilcnte')]
    public function accueilcnte(

    ): Response{
        return $this->redirectToRoute('main_accueil');

    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/profil/modifier/{id}',
        name:'_modifierprofil'
    )]
    public function modifierProfil(
        EntityManagerInterface $entityManager,
        Request $request,
        Participant $id
    ) : Response{

        $participantForm = $this->createForm(ParticipantType::class,$id);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            try{
                $entityManager->persist($id);
                $entityManager->flush();
                $this->redirectToRoute('participant_accueilcnte');
                $this->addFlash('success','Votre profil a bien été modifié');
                return $this->redirectToRoute('participant_accueilcnte');
            }catch (\Exception $exception){
                $this->addFlash('danger','La modification n\'a pas été effectué'.$exception->getMessage());
                return $this->redirectToRoute('participant_modifierprofil',['id'=>$id->getId()]);
            }

        }
        return $this->render('participant/modifier.html.twig',compact('participantForm'));
    }
}
