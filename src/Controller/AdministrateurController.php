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
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(
    '/administrateur',
    name: 'admin'
)]
class AdministrateurController extends AbstractController{
    /**
     * @return Response : envoie vers la page d'accueil administrateur
     */
    #[Route(
        '/',
        name: '_accueil'
    )]
    public function accueil(
    ) : Response{

        return $this->render('administrateur/acceuil.html.twig');
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Request $requestInscriptionUtilisateur
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManagerInscriptionUtilisateur
     * @param ParticipantRepository $participantsRepository
     * @param MailerInterface $mailer
     * @return Response : renvoie vers l'affichage de la liste des utilisateurs et formulaire
     * @throws \Exception : si erreur lors de l'accès à la base données
     * @throws TransportException si erreur lors de l'envoie du mail
     */
    #[Route(
        '/gestionutilisateur',
        name: '_gestionutilisateur'
    )]
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
                $this->addFlash('success','L\'inscription a bien été enregistrée');
                return $this->redirectToRoute('admin_accueil');

            }catch (\Exception $exception){
                $this->addFlash('danger','L\'inscription n\'a pas été effectuée'.$exception->getMessage());
                return $this->redirectToRoute('admin_gestionutilisateur');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger','L\'envoie du(des) mail(s) a échoué');
            }
        }
        return $this->render('administrateur/gestionutilisateur.html.twig', [
            'registrationForm' => $formUtilisateur->createView(),
            'participants' => $participants,
        ]);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param EntityManagerInterface $entityManager
     * @param Participant $participant
     * @param MailerInterface $mailer
     * @return Response
     * @throws \Exception : si erreur lors de l'accès à la base données
     * @throws TransportException si erreur lors de l'envoie du mail
     */
    #[Route('/statut/{participant}',
        name: '_statut'
    )]
    public function statutInactif(
        EntityManagerInterface $entityManager,
        Participant $participant,
        MailerInterface $mailer

    ): Response{
        if ($participant->isActif()) {
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
                    ->to($participant->getEmail())
                    ->subject('Nous avons "désactimisé" votre compte')
                    ->text('Cher utilisateur,
                            
                            Nous sommes désolés de vous informer que votre compte a été "désactimisé" (oui, nous avons inventé ce mot) pour non-respect de nos conditions d\'utilisation.

                            Nous sommes conscients que ce n\'est pas une bonne nouvelle, mais il y a toujours une lueur d\'espoir ! 
                            Vous pouvez prendre cette occasion pour vous reposer, passer du temps avec votre famille et vos amis, lire un bon livre ou regarder une série sur Netflix. 
                            Ou vous pouvez nous contacter pour discuter de la manière dont vous pouvez récupérer votre compte.

                            Nous vous remercions de votre compréhension et de votre coopération. Nous espérons vous revoir bientôt sur notre plateforme, et cette fois-ci, en respectant les règles !

                            Cordialement,
                            L\'équipe des SortiesEnitiennes.com');

                $mailer->send($email);
                $participant->setActif(false);
                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash("success", "Le statut du compte de " . $participant->getPseudo() . " est désormais inactif");
                return $this->redirectToRoute('admin_gestionutilisateur');
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible de désactiver le compte");
                return $this->redirectToRoute('admin_gestionutilisateur');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger','L\'envoie du(des) mail(s) a échoué');
            }
        }
        else {
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
        return $this->render('administrateur/gestionutilisateur.html.twig');
    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param EntityManagerInterface $entityManager
     * @param Participant $participant
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/supprimer/{participant}',
        name: '_supprimer'
    )]
    public function supprimerUtilisateur(
        EntityManagerInterface $entityManager,
        Participant $participant

    ): Response{

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

    /**
     * @param SortieRepository $sortieRepository
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param EtatRepository $etatRepository
     * @return Response : envoie vers l'affichage des sorties
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/gestionsorties',
        name: '_gestionSorties'
    )]
    public function adminGestionSortie(
        SortieRepository $sortieRepository,
        Request $request,
        FormFactoryInterface $formFactory,
        EtatRepository $etatRepository

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

        } catch (\Exception $exception) {
            $this->addFlash('danger', "Impossible d'afficher les sorties demandées");
        }
        return $this->render('administrateur/gestionSorties.html.twig', [
                'formFilterSortie' => $formFilterSortie->createView(),
                'sorties' => $sorties,
            ]);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param Request $requestFilter
     * @param Request $requestAjout
     * @param CampusRepository $campusRepository
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $entityManager
     * @return Response : renvoie vers affichage des campus
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
            '/gestioncampus',
            name: '_gestionCampus'
    )]
       public function gestionCampus(
            Request $requestFilter,
            Request $requestAjout,
            CampusRepository $campusRepository,
            FormFactoryInterface $formFactory,
            EntityManagerInterface $entityManager,

    ): Response{
            $filterCampus = new FilterCampus();
            $formFilterCampus = $formFactory->create(FilterCampusType::class, $filterCampus);
            $formFilterCampus->handleRequest($requestFilter);

        try {

            $campuss = $campusRepository->filterListeCampus($filterCampus);

        } catch (\Exception $exception) {
            $this->addFlash('danger', "Impossible d'afficher les campus damandées");
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
                $this->addFlash('danger','Le nouveau campus n\'a pas pu être ajouté');
                return $this->redirectToRoute('admin_gestionCampus');
            }
        }
        return $this->render('administrateur/gestioncampus.html.twig', [
                'formFilterCampus' => $formFilterCampus->createView(),
                'campusForm' => $formCampus->createView(),
                'campuss' => $campuss
            ]);
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param Campus $campus
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/suppressioncampus/{campus}',
        name: '_suppressionCampus'
    )]
    public function adminSuppressionCampus(
        Campus  $campus,
        EntityManagerInterface $entityManager

    ) : Response{

            try{
                $entityManager->remove($campus);
                $entityManager->flush();
                $this->addFlash('success', "Le campus de a bien été supprimée" );
                return $this->redirectToRoute('admin_gestionCampus');
            }catch (\Exception $exception) {
                $this->addFlash('danger', "La suppression du campus n'a pas été effectuée");
                return $this->redirectToRoute('admin_gestionCampus', [
                    'campus' => $campus->getId()
                ]);
            }
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Campus $campus
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/modifiercampus/{campus}',
        name: '_modifierCampus'
    )]
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
                $this->addFlash('danger','Le nouveau campus n\'a pas pu être ajouté');
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

    /**
     * @param Request $requestFilter
     * @param Request $requestAjout
     * @param VilleRepository $villeRepository
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/gestionville',
        name: '_gestionVille'
    )]
    public function gestionVille(
        Request $requestFilter,
        Request $requestAjout,
        VilleRepository $villeRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,

    ): Response{
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

    /**
     * @param Ville $ville
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/suppressionville/{ville}',
        name: '_suppressionVille')]
    public function adminSuppressionVille(
        Ville  $ville,
        EntityManagerInterface $entityManager,
    ) : Response{
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

    /**
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Ville $ville
     * @return Response
     * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/modifierville/{ville}',
        name: '_modifierVille'
    )]
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
