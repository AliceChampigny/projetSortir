<?php

namespace App\Controller;


use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\CampusType;
use App\Form\FilterCampusType;
use App\Form\FilterType;
use App\Form\FilterVilleType;
use App\Form\RegistrationFormType;
use App\Form\VilleType;
use App\modeles\Filter;
use App\modeles\FilterCampus;
use App\modeles\FilterVille;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        MailerInterface $mailer

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
                $email = (new TemplatedEmail())
                    ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
                    ->to($user->getEmail())
                    ->subject('Création d\'un compte pour'.$user->getNom().' '. $user->getPrenom())
                    ->text('Bonjour !

                            Vous bénéficiez maintenant d\'un compte utilisateur sur le site SortiesEnitiennes.com.
                            Vous pouvez vous connecter grâce aux identifiants suivants :
                            Pseudo : '. $user->getPseudo() .'
                            Email : '. $user->getEmail() .'
                            Mot de Passe : '. $formUtilisateur->get('plainPassword')->getData() .'
                            Nous vous conseillons vivement de changer votre mot de passe lors de votre première connection.

                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
                $mailer->send($email);

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
        MailerInterface $mailer
    ): Response
    {
        if ($participant->isActif()) {
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
                    ->to($participant->getEmail())
                    ->subject('Désactivation de votre compte')
                    ->text('Bonjour !
                            
                            Nous constatons que vous annulez de manière systématique les sorties que vous proposez.
                            Cela a malheureusement des conséquenses désagréables pour nos utilisateurs.
                            Votre compte a donc été désactivé.
                            

                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
                $mailer->send($email);
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
//----------------------------------------------------------------------------------------------------------------------
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

        $ville = new Ville();
        $formVille = $this->createForm(VilleType::class, $ville);
        $formVille->handleRequest($requestAjout);

        if ($formVille->isSubmitted() && $formVille->isValid()) {
            try{
                $entityManager->persist($ville);
                $entityManager->flush();
                $this->addFlash('danger','La ville a bien été enregistrée');
                return $this->redirectToRoute('admin_gestionVille');
            }catch (\Exception $exception){
                $this->addFlash('danger','La nouvel ville n\'a pas pu être ajouté'.$exception->getMessage());
                return $this->redirectToRoute('admin_gestionVille');
            }
        }
        return $this->render('administrateur/gestionville.html.twig', [
                'formFilterVille' => $formFilterVille->createView(),
                'villeForm' => $formVille->createView(),
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
//----------------------------------------------------------------------------------------------------------------------
    #[Route(
        '/modifierville/{ville}',
        name: '_modifierVille')]
    public function modifierVille(
        EntityManagerInterface $entityManager,
        Request                $request,
        Ville                 $ville,

    ) : Response{

        $formVille = $this->createForm(VilleType::class, $ville);
        $formVille->handleRequest($request);

        if ($formVille->isSubmitted() && $formVille->isValid()) {
            try{
                $entityManager->persist($ville);
                $entityManager->flush();
                $this->addFlash('danger','Le ville a bien été enregistrée');
                return $this->redirectToRoute('admin_gestionVille');
            }catch (\Exception $exception){
                $this->addFlash('danger','La nouvelle ville n\'a pas pu être ajoutée'.$exception->getMessage());
                return $this->redirectToRoute('admin_modifierVille');
            }
        }
        return $this->render('administrateur/modifierville.html.twig', [
                'ville' => $ville->getId(),
                'villeForm' => $formVille->createView(),
            ]
        );

    }

}
