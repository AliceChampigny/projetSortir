<?php

namespace App\Controller;


use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\CampusType;
use App\Form\FileUploadType;
use App\Form\FilterCampusType;
use App\Form\FilterType;
use App\Form\FilterVilleType;
use App\Form\RegistrationFormType;
use App\Form\SortieFormType;
use App\modeles\Filter;
use App\modeles\FilterCampus;
use App\modeles\FilterVille;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Services\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
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
        Request $request,
        FormFactoryInterface $formFactory,
        EtatRepository $etatRepository,
    ) : Response
    {
  try {
            $filter = new Filter();
            $formFilterSortie = $formFactory->create(FilterType::class, $filter);
            $formFilterSortie->handleRequest($request);
            $userConnecte = $this->getUser();
            $id = 5;
            $sortiesPassees = $etatRepository->find($id);
            $sorties = $sortieRepository -> filtreListeSorties($filter, $userConnecte, $sortiesPassees);
//         return $this->redirectToRoute('sortie_liste');
        } catch (\Exception $exception) {
    $this->addFlash('danger', "Impossible d'afficher les sorties damandées" . $exception->getMessage());
}
            return $this->render('administrateur/gestionSorties.html.twig', [
                    'formFilterSortie' => $formFilterSortie->createView(),
                    'sorties' => $sorties,

                ]
            );
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
                    foreach ($sortie->getParticipants() as $participant) {
                        $mailParticipants->add(new Address($participant->getEmail()));
                    }
                    $email = (new TemplatedEmail())
                        ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
                        ->to(...$mailParticipants)
                        ->subject('Annulation de la sortie '.$sortie->getNom())
                        ->text('Bonjour !

                            L\'administrateur du site SortiesEnniciennes.com vient juste d\'annuler la sortie '.$sortie->getNom().'. Le motif d\'annulation est disponible sur notre au site au niveau du détails de la sortie.
                            Nous nous excusons pour la gêne occasionnée.

                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
                    $mailer->send($email);

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

//----------------------------------------------------------------------------------------------------------------------

#[Route(
        '/gestioncampus',
        name: '_gestionCampus')]
   public function gestionCampus(
        Request $requestFilter,
        Request $requestAjout,
        CampusRepository $campusRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,

): Response
{
        $filterCampus = new FilterCampus();
        $formFilterCampus = $formFactory->create(FilterCampusType::class, $filterCampus);
        $formFilterCampus->handleRequest($requestFilter);

    try {

        $campuss = $campusRepository->filterListeCampus($filterCampus);

    } catch (\Exception $exception) {
        $this->addFlash('danger', "Impossible d'afficher les campus damandées" . $exception->getMessage());
    }

    $campus = new Campus();
    $formCampus = $this->createForm(CampusType::class, $campus);
    $formCampus->handleRequest($requestAjout);

    if ($formCampus->isSubmitted() && $formCampus->isValid()) {
        try{
            $entityManager->persist($campus);
            $entityManager->flush();
            $this->addFlash('danger','Le campus a bien été enregistrée');
            return $this->redirectToRoute('admin_gestionCampus');
        }catch (\Exception $exception){
            $this->addFlash('danger','Le nouveau campus n\'a pas pu être ajouté'.$exception->getMessage());
            return $this->redirectToRoute('admin_gestionCampus');
        }
    }
    return $this->render('administrateur/gestioncampus.html.twig', [
            'formFilterCampus' => $formFilterCampus->createView(),
            'campusForm' => $formCampus->createView(),
            'campuss' => $campuss
        ]
    );
}
//----------------------------------------------------------------------------------------------------------------------

    #[Route(
        '/suppressioncampus/{campus}',
        name: '_suppressionCampus')]
    public function adminSuppressionCampus(
        Campus  $campus,
        EntityManagerInterface $entityManager,
    ) : Response
    {
            try{
                $entityManager->remove($campus);
                $entityManager->flush();
                $this->addFlash('success', "Le campus de a bien été supprimée" );
                return $this->redirectToRoute('admin_gestionCampus');
            }catch (\Exception $exception) {
                $this->addFlash('danger', "La suppression du campus n'a pas été effectuée" . $exception->getMessage());
                return $this->redirectToRoute('admin_gestionCampus', [
                    'campus' => $campus->getId()
                ]);
            }
    }

    #[Route(
        '/modifiercampus/{campus}',
        name: '_modifierCampus')]
    public function modifierSortie(
        EntityManagerInterface $entityManager,
        Request                $request,
        Campus                 $campus,

    ) : Response{


        $formCampus = $this->createForm(CampusType::class, $campus);
        $formCampus->handleRequest($request);

        if ($formCampus->isSubmitted() && $formCampus->isValid()) {
            try{
                $entityManager->persist($campus);
                $entityManager->flush();
                $this->addFlash('danger','Le campus a bien été enregistrée');
                return $this->redirectToRoute('admin_gestionCampus');
            }catch (\Exception $exception){
                $this->addFlash('danger','Le nouveau campus n\'a pas pu être ajouté'.$exception->getMessage());
                return $this->redirectToRoute('admin_modifierCampus');
            }
        }
        return $this->render('administrateur/modifiercampus.html.twig', [
                'campus' => $campus->getId(),
                'campusForm' => $formCampus->createView(),
            ]
        );

    }


    //----------------------------------------------------------------------------------------------------------------------

    #[Route(
        '/gestionville',
        name: '_gestionVille')]
    public function gestionVille(
        Request $requestFilter,
        Request $requestAjout,
        VilleRepository $villeRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,

    ): Response
    {
        $filterVille = new FilterVille();
        $formFilterVille = $formFactory->create(FilterVilleType::class, $filterVille);
        $formFilterVille->handleRequest($requestFilter);

        try {

            $villes = $villeRepository->filterListeVille($filterVille);

        } catch (\Exception $exception) {
            $this->addFlash('danger', "Impossible d'afficher les villes damandées" . $exception->getMessage());
        }

//        $ville = new Ville();
//        $formVille = $this->createForm(VilleType::class, $ville);
//        $formVille->handleRequest($requestAjout);
//
//        if ($formVille->isSubmitted() && $formVille->isValid()) {
//            try{
//                $entityManager->persist($ville);
//                $entityManager->flush();
//                $this->addFlash('danger','La ville a bien été enregistrée');
//                return $this->redirectToRoute('admin_gestionVille');
//            }catch (\Exception $exception){
//                $this->addFlash('danger','La nouvel ville n\'a pas pu être ajouté'.$exception->getMessage());
//                return $this->redirectToRoute('admin_gestionVille');
//            }
//        }
        return $this->render('administrateur/gestionville.html.twig', [
                'formFilterVille' => $formFilterVille->createView(),
//                'villeForm' => $formVille->createView(),
                'villes' => $villes
            ]
        );
    }
//----------------------------------------------------------------------------------------------------------------------

    #[Route(
        '/suppressionville/{ville}',
        name: '_suppressionVille')]
    public function adminSuppressionVille(
        Ville  $ville,
        EntityManagerInterface $entityManager,
    ) : Response
    {
        try{
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', "La ville a bien été supprimée" );
            return $this->redirectToRoute('admin_gestionVille');
        }catch (\Exception $exception) {
            $this->addFlash('danger', "La suppression de la ville n'a pas été effectuée" . $exception->getMessage());
            return $this->redirectToRoute('admin_gestionVille', [
                'ville' => $ville->getId()
            ]);
        }
    }

//    #[Route(
//        '/modifierville/{ville}',
//        name: '_modifierVille')]
//    public function modifierVille(
//        EntityManagerInterface $entityManager,
//        Request                $request,
//        Ville                 $ville,
//
//    ) : Response{
//
//
//        $formVille = $this->createForm(VilleType::class, $ville);
//        $formVille->handleRequest($request);
//
//        if ($formVille->isSubmitted() && $formVille->isValid()) {
//            try{
//                $entityManager->persist($ville);
//                $entityManager->flush();
//                $this->addFlash('danger','Le ville a bien été enregistrée');
//                return $this->redirectToRoute('admin_gestionVille');
//            }catch (\Exception $exception){
//                $this->addFlash('danger','La nouvelle ville n\'a pas pu être ajoutée'.$exception->getMessage());
//                return $this->redirectToRoute('admin_modifierVille');
//            }
//        }
//        return $this->render('administrateur/modifierville.html.twig', [
//                'ville' => $ville->getId(),
//                'villeForm' => $formVille->createView(),
//            ]
//        );
//
//    }

}
