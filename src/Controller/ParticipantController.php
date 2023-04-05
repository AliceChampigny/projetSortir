<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(
    '/participant',
    name:'participant'
)]
class ParticipantController extends AbstractController{

    /**
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Participant $id
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param SluggerInterface $slugger
     * @return Response : dirige vers le formulaire de modification, puis si validation du formulaire, renvoie vers la page d'accueil
     * @throws FileException : si le téléchargement de l'image s'est mal passée
     * @throws \Exception : si l'entrée des données en base a échoué
     */
    #[Route(
        '/profil/modifier/{id}',
        name:'_modifierprofil',
        requirements: ['id' => '\d+']
    )]
    public function modifierProfil(
        EntityManagerInterface $entityManager,
        Request $request,
        Participant $id,
        UserPasswordHasherInterface $userPasswordHasher,
        SluggerInterface $slugger

    ) : Response{

        $participantForm = $this->createForm(ParticipantType::class, $id);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()){
            $imageFile = $participantForm->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('photo'),
                        $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger','Erreur lors du téléchargement de l\'image');
                }
                $id->setImageName($newFilename);
            }

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
                $this->addFlash('danger','La modification n\'a pas été effectuée');
                return $this->redirectToRoute('participant_modifierprofil',['id'=>$id->getId()]);
            }
        }
        return $this->render('participant/modifier.html.twig',compact('participantForm'));
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Participant $id
     * @param ParticipantRepository $participantRepository
     * @return Response : envoie vers la page d'affichage du profil du participant
     */
    #[Route(
        '/profil/{id}',
        name: '_afficherprofil',
        requirements: ['id' => '\d+']
    )]
    public function afficherProfil(
        Participant $id,
        ParticipantRepository $participantRepository

    ) : Response{

            $participant = $participantRepository->findOneBy(['id'=>$id->getId()]);
            return $this->render('participant/afficherprofil.html.twig',compact('participant'));
    }
//----------------------------------------------------------------------------------------------------------------------

}
