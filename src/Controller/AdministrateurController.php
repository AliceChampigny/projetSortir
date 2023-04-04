<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\RegistrationFormType;
use App\Form\SortieFormType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/administrateur',
    name: 'admin')]
class AdministrateurController extends AbstractController
{
    #[Route(
        '/',
        name: '_accueil')]
    public function accueil(

    ) : Response{

        return $this->render('administrateur/acceuil.html.twig');
    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/gestionutilisateur',
        name: '_gestionutilisateur')]
    public function gestionutilisateur(
        Request $requestInscriptionUtilisateur,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManagerInscriptionUtilisateur,
        ParticipantRepository $participantsRepository,

    ): Response
    {
        $user = new Participant();
        $user -> setActif(true);
        $user -> setAdministrateur(false);
        $formUtilisateur = $this->createForm(RegistrationFormType::class, $user);
        $formUtilisateur->handleRequest($requestInscriptionUtilisateur);
        $participants = $participantsRepository -> findAll();
        if ($formUtilisateur->isSubmitted() && $formUtilisateur->isValid()) {
            try{
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $formUtilisateur->get('plainPassword')->getData()
                    )
                );
                $entityManagerInscriptionUtilisateur->persist($user);
                $entityManagerInscriptionUtilisateur->flush();
                $this->addFlash('danger','L\'inscription a bien été enregistrée');
                return $this->redirectToRoute('admin_accueil');
            }catch (\Exception $exception){
                $this->addFlash('danger','L\'inscription n\'a pas été effectuée'.$exception->getMessage());
                return $this->redirectToRoute('admin_gestionutilisateur');
            }
        }
        return $this->render('administrateur/gestionutilisateur.html.twig', [
            'registrationForm' => $formUtilisateur->createView(),
            'participants' => $participants,
        ]);
    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route('/statut/{participant}',
        name: '_statut')]
    public function statutInactif(

        EntityManagerInterface $entityManager,
        Participant $participant,
    ): Response
    {
        if ($participant->isActif()) {
            try {
                $participant->setActif(false);
                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash("success", "Le statut du compte de " . $participant->getPseudo() . " est désormais inactif");
                return $this->redirectToRoute('admin_gestionutilisateur');
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible de désactiver le compte");
                return $this->redirectToRoute('admin_gestionutilisateur');
            }
        } else {
            try {
                $participant->setActif(true);
                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash("success", "Le statut du compte de " . $participant->getPseudo() . " est désormais actif");
                return $this->redirectToRoute('admin_gestionutilisateur');
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible d'activer le compte'");
                return $this->redirectToRoute('admin_gestionutilisateur');
            }
        }
    }

//----------------------------------------------------------------------------------------------------------------------
    #[Route('/supprimer/{participant}',
        name: '_supprimer')]
    public function supprimerUtilisateur(

        EntityManagerInterface $entityManager,
        Participant $participant,
    ): Response
    {
            try {
                $entityManager->remove($participant);
                $entityManager->flush();
                $this->addFlash("success", "Le compte a été supprimé");
                return $this->redirectToRoute('admin_gestionSorties');
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible de supprimer le compte");
                return $this->redirectToRoute('admin_gestionSorties');
            }

    }

//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/gestionsorties',
        name: '_gestionSorties')]
    public function adminGestionSortie(
        SortieRepository $sortieRepository,
    ) : Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('administrateur/gestionSorties.html.twig', compact('sorties'));
    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/suppressionsorties/{sortie}',
        name: '_suppressionSorties')]
    public function adminSuppressionSortie(
        Sortie  $sortie,
        EntityManagerInterface $entityManager,
    ) : Response
    {
        if($sortie->getEtat()->getId()===1 ){
            try{
                $entityManager->remove($sortie);
                $entityManager->flush();
                $this->addFlash('success', "Votre sortie a bien été supprimée" );
                return $this->redirectToRoute('admin_gestionSorties');
            }catch (\Exception $exception) {
                $this->addFlash('danger', "La suppression n'a pas été effectuée" . $exception->getMessage());
                return $this->redirectToRoute('admin_gestionSorties', [
                    'sortie' => $sortie->getId()
                ]);
            }
        } else{
            $this->addFlash('danger', "La suppression d'une sortie dont l'état est autre que 'Créée', est impossible" );
            return $this->redirectToRoute('admin_gestionSorties');
        }
    }
//----------------------------------------------------------------------------------------------------------------------

#[Route(
    '/annulationSortie/{sortie}',
    name: '_annulationSortie')]
    public function adminAnnulerSortie(
    Sortie $sortie,
    EntityManagerInterface $entityManager,
    EtatRepository $etatRepository,
    Request $request,
    MailerInterface $mailer
) : Response{

        if($sortie->getEtat()->getId()=== 2 || $sortie->getEtat()->getId()=== 3  ){
            $sortie->setInfosSortie( '');
            $sortieForm = $this->createForm(SortieFormType::class,$sortie);
            $sortieForm->handleRequest($request);

            if($sortieForm->isSubmitted() && $sortieForm->isValid()){
                try{
                    $etat = $etatRepository->findOneBy(['id' => 6]);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre sortie a bien été annulée");
                    $mailParticipants = new ArrayCollection();
//                    foreach ($sortie->getParticipants() as $participant) {
//                        $mailParticipants->add(new Address($participant->getEmail()));
//                    }
//                    $email = (new TemplatedEmail())
//                        ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
//                        ->to(...$mailParticipants)
//                        ->subject('Annulation de la sortie '.$sortie->getNom())
//                        ->text('Bonjour !
//
//                            L\'organisateur de la sortie '.$sortie->getNom().' vient juste de l\'annuler. Le motif d\'annulation est disponible sur notre au site au niveau du détails de la sortie.
//                            Nous nous excusons pour la gêne occasionnée.
//
//                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
//                    $mailer->send($email);

                    return $this->redirectToRoute('admin_gestionSorties');

                }catch(\Exception $exception){
                    $this->addFlash('danger', "L\'annulation n'a pas été effectuée". $exception->getMessage() );
                    return $this->redirectToRoute('sortie_annulersortie',[
                        'sortie' =>$sortie->getId()
                    ]);
                } catch (TransportExceptionInterface $e) {
                }
            }
            return $this->render('sortie/annulersortie.html.twig',compact('sortieForm','sortie'));
        } else{
            $this->addFlash('danger', 'L\'annulation d\'une sortie dont l\'état est autre que "Ouverte" ou "Cloturée" est impossible' );
            return $this->redirectToRoute('admin_gestionSorties');
        }
    }
}
