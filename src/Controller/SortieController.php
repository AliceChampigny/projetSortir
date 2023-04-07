<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FilterType;
use App\Form\SortieFormType;
use App\modeles\Filter;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route(
    '/sortie',
    name:'sortie'
)]
class SortieController extends AbstractController{

    public const CREEE = 1;
    public const OUVERTE = 2;
    public const CLOTUREE = 3;
    public const EN_COURS = 4;
    public const PASSEE = 5;
    public const ANNULEE = 6;
    public const ARCHIVEE = 7;
//----------------------------------------------------------------------------------------------------------------------

    /**
     * @param SortieRepository $sortieRepository
     * @param Request $request
     * @param FormFactoryInterface $formFactory
     * @param EtatRepository $etatRepository
     * @return Response :vers page d'accueil/liste de sorties
      * @throws \Exception : si erreur lors de l'accès aux données
     */
    #[Route(
        '/',
        name: '_liste'
    )]
    public function ListeSorties(
        SortieRepository $sortieRepository,
        Request $request,
        FormFactoryInterface $formFactory,
        EtatRepository $etatRepository,

    ): Response{

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
            return $this->render('main/accueil.html.twig',compact('formFilterSortie','sorties'));
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Participant $organisateur
     * @param EtatRepository $etatRepository
     * @return Response : envoie vers la page de formulaire par défaut, si validation du formulaire, redirige vers page d'accueil
     * @throws \Exception : si erreur lors de l'entrée des données en base
     */
    #[Route(
        '/ajouter/{organisateur}',
        name: '_ajouter',
        requirements: ['organisateur' => '\d+']
    )]
    public function ajouterunesortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        Participant $organisateur,
        EtatRepository $etatRepository,
        SluggerInterface $slugger

    ): Response{

        $sortie = new Sortie();
        $sortie->setDateHeureDebut(new \DateTime('+ 2 days'));//valeurs par défaut
        $sortie->setDateLimiteInscription(new \DateTime('+1 day'));
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                $etat = $etatRepository->findOneBy(['id'=>self::CREEE]); //id de l'état Créée
                $sortie->setCampus($organisateur->getCampus());
                $etat->addSorty($sortie);
                $organisateur->addSortiesOrganisee($sortie);

                $sortieImage = $sortieForm->get('sortieImage')->getData();

                if ($sortieImage) {
                    $originalFilename = pathinfo($sortieImage->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$sortieImage->guessExtension();

                    try {

                        $sortieImage->move(
                            $this->getParameter('photo'),
                            $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('danger','Erreur lors du téléchargement de l\'image');
                    }
                    $sortie->setNomImageSortie($newFilename);
                }

                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', "Votre sortie a été ajoutée" );
                return $this->redirectToRoute('sortie_liste');

            } catch (\Exception $exception){
                $this->addFlash('danger', "Votre sortie n'a pas été ajoutée");
                return $this->redirectToRoute('sortie_ajouter',[
                    'organisateur'=>$organisateur->getId()
                ]);
            }
        }
        return $this->render('sortie/ajouterunesortie.html.twig',
            compact('sortieForm'));
    }

//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param EntityManagerInterface $entityManager
     * @param Sortie $sortie
     * @return Response : redirige vers la page d'accueil
     * @throws \Exception : si erreur lors de l'entrée des données en base
     */
    #[Route(
        '/inscription/{sortie}',
        name: '_inscription',
        requirements: ['sortie' => '\d+']
    )]
    public function inscriptionsorties(
        EntityManagerInterface $entityManager,
        Sortie                 $sortie

    ): Response{

        if ($sortie && $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()) {
            try {
                $participant = $this->getUser();
                $sortie->addParticipant($participant);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash("success", "Votre inscription a bien été enregistrée");
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible de vous inscrire");
            }
        }
        else{
            $this->addFlash("danger", "Votre inscription est impossible : soit la sortie n'existe pas, soit il n'y a plus de place disponible");
        }
        return $this->redirectToRoute('sortie_liste');
    }

//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Sortie $sortie
     * @param SortieRepository $sortieRepository
     * @return Response : dirige vers la page d'affichage d'une sortie
     */
    #[Route(
        '/sortie/{sortie}',
        name: '_affichersortie',
        requirements: ['sortie' => '\d+']
    )]
    public function afficherSortie(
        Sortie          $sortie,
        SortieRepository $sortieRepository
    ): Response{

        $sortie = $sortieRepository->findOneby(['id' => $sortie->getId()]);

        return $this->render('sortie/affichersortie.html.twig',
            compact('sortie')
        );
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param EntityManagerInterface $entityManager
     * @param Sortie $sortie
     * @return Response :dirige vers la page d'affichage d'une sortie
     * @throws \Exception : si erreur lors de l'entrée des données en base
     */
    #[Route(
        '/desistement/{sortie}',
        name: '_desistement',
        requirements: ['sortie' => '\d+']
    )]
    public function desistementSorties(
        EntityManagerInterface $entityManager,
        Sortie                 $sortie

    ): Response{

        if ($sortie) {
            try {
                $participant = $this->getUser();
                $sortie->removeParticipant($participant);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash("success", "Votre désistement a bien été pris en compte");
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Impossible de vous désister");
            }
        }
        return $this->redirectToRoute('sortie_liste');
    }

//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Sortie $sortie
     * @param VilleRepository $villeRepository
     * @return Response : dirige vers la page de modification d'une sortie, puis si validation du formulaire, retour vers la page d'accueil
     * @throws \Exception : si les nouvelles données non transmises en base de données
     */
    #[Route(
        '/modifier/{sortie}',
        name: '_modifiersortie',
        requirements: ['sortie' => '\d+']
    )]
    public function modifierSortie(
        EntityManagerInterface $entityManager,
        Request                $request,
        Sortie                 $sortie,
        VilleRepository        $villeRepository

    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser()){
            if($sortie->getEtat()->getId()===self::CREEE ){
                $sortieForm = $this->createForm(SortieFormType::class,$sortie);
                $ville = $villeRepository->findOneBy(['id'=>$sortie->getLieu()->getVille()->getId()]);
                $sortieForm->handleRequest($request);

                if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

                    try {
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                        $this->addFlash('success', "Votre sortie a bien été modifiée" );
                        return $this->redirectToRoute('sortie_liste');
                    } catch (\Exception $exception){
                        $this->addFlash('danger', "Les modifications n'ont pas été effectuées");
                        return $this->redirectToRoute('sortie_modifiersortie',[
                            'sortie' =>$sortie->getId()
                        ]);
                    }
                }
                return $this->render('sortie/modifier.html.twig',compact('sortieForm', 'sortie','ville'));
            }else{
                $this->addFlash('danger', "La modification d'une sortie dont l'état est autre que 'Créée', 
                                                                est impossible" );
                return $this->redirectToRoute('sortie_liste');
            }
        }else{
            $this->addFlash('danger', "La modification d'une sortie 
                                                    dont vous n'êtes pas l'organisateur est impossible" );
            return $this->redirectToRoute('sortie_liste');
        }
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Sortie $sortie
     * @param EntityManagerInterface $entityManager
     * @return Response : redirige vers la page d'accueil
     * @throws \Exception si suppression non effectuée en base de données
     */
    #[Route(
        '/supprimer/{sortie}',
        name: '_supprimersortie',
        requirements: ['sortie' => '\d+']
    )]
    public function supprimerSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager

    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser() || in_array('ROLE_ADMIN',$this->getUser()->getRoles())){
            if($sortie->getEtat()->getId()===self::CREEE ){
                try{

                    $entityManager->remove($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre sortie a bien été supprimée" );
                    return $this->redirectToRoute('sortie_liste');

                }catch (\Exception $exception) {
                    $this->addFlash('danger', "La suppression n'a pas été effectuée");
                    return $this->redirectToRoute('sortie_supprimersortie', [
                        'sortie' => $sortie->getId()
                    ]);
                }
            }
            else{
                $this->addFlash('danger', "La suppression d'une sortie dont l'état est autre que 'Créée', 
                                                                est impossible" );
                return $this->redirectToRoute('sortie_liste');
            }
        }else{
            $this->addFlash('danger', "La suppression d'une sortie 
                                                    dont vous n'êtes pas l'organisateur est impossible" );
            return $this->redirectToRoute('sortie_liste');
        }
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Sortie $sortie
     * @param EntityManagerInterface $entityManager
     * @param EtatRepository $etatRepository
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response : dirige vers le formulaire d'annulation, puis si validation du formulaire, retour vers la page d'accueil
     * @throws \Exception : si annulation non effectuée en base de données
     */
    #[Route(
        '/annuler/{sortie}',
        name: '_annulersortie',
        requirements: ['sortie' => '\d+']
    )]
    public function annulerSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        Request $request,
        MailerInterface $mailer

    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser() || in_array('ROLE_ADMIN',$this->getUser()->getRoles())){
            if($sortie->getEtat()->getId()=== self::OUVERTE || $sortie->getEtat()->getId()=== self::CLOTUREE ){
                $sortie->setInfosSortie( '');
                $sortieForm = $this->createForm(SortieFormType::class,$sortie);
                $sortieForm->handleRequest($request);

                if($sortieForm->isSubmitted() && $sortieForm->isValid()){

                    try{

                        $etat = $etatRepository->findOneBy(['id' => self::ANNULEE]);
                        $sortie->setEtat($etat);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                        $this->addFlash('success', "Votre sortie a bien été annulée");
                        if($sortie->getParticipants()->count()>0){
                            $mailParticipants = new ArrayCollection();
                            foreach ($sortie->getParticipants() as $participant) {
                                $mailParticipants->add(new Address($participant->getEmail()));
                            }
                            $email = (new TemplatedEmail())
                                ->from(new Address('admin@campus-eni.fr', 'Administrateur de "SortiesEnitiennes.com"'))
                                ->to(...$mailParticipants)
                                ->subject('Annulation de la sortie '.$sortie->getNom())
                                ->text('Bonjour !
                            
                            La sortie '.$sortie->getNom().' vient juste de l\'annuler. Le motif d\'annulation est disponible sur notre au site au niveau du détails de la sortie.
                            Nous nous excusons pour la gêne occasionnée.
                             
                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
                            $mailer->send($email);
                        }
                        return $this->redirectToRoute('sortie_liste');

                    }catch(\Exception $exception){
                        $this->addFlash('danger', "L'annulation n'a pas été effectuée");
                        return $this->redirectToRoute('sortie_annulersortie',[
                            'sortie' =>$sortie->getId()
                        ]);
                    } catch (TransportExceptionInterface $e) {
                    }
                }
                return $this->render('sortie/annulersortie.html.twig',compact('sortieForm','sortie'));

            }
            else{
                $this->addFlash('danger', 'L\'annulation d\'une sortie dont l\'état est autre que
                                                        "Ouverte" ou "Cloturée" est impossible' );
                return $this->redirectToRoute('sortie_liste');
            }
        }
        else{
            $this->addFlash('danger', 'L\'annulation d\'une sortie
                                                    dont vous n\'êtes pas l\'organisateur est impossible' );
            return $this->redirectToRoute('sortie_liste');
        }
    }
//----------------------------------------------------------------------------------------------------------------------
    /**
     * @param Sortie $sortie
     * @param EntityManagerInterface $entityManager
     * @param EtatRepository $etatRepository
     * @return Response : renvoie vers la page d'accueil
     * @throws \Exception si erreur lors du changement d'etat en base de données
     */
    #[Route(
        '/publier/{sortie}',
        name: '_publiersortie'
    )]
    public function publierSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository

    ):Response{

        if($sortie->getOrganisateur() === $this->getUser() ){
            if($sortie->getEtat()->getId()===self::CREEE ){

                try{
                    $etat = $etatRepository->findOneBy(['id' => self::OUVERTE]);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre sortie a bien été publiée");
                    $this->redirectToRoute('sortie_liste');
                }catch (\Exception $exception){
                    $this->addFlash('danger', "La publication n'a pu être effectuée");
                    return $this->redirectToRoute('sortie_publiersortie',[
                        'sortie' =>$sortie->getId()
                    ]);
                }
            }
            else{
                $this->addFlash('danger', 'La publication d\'une sortie dont l\'état est autre que
                                                        "Créée" est impossible' );
                return $this->redirectToRoute('sortie_liste');
            }
        }
        else{
            $this->addFlash('danger', 'La publication d\'une sortie
                                                    dont vous n\'êtes pas l\'organisateur est impossible');
        }
        return $this->redirectToRoute('sortie_liste');
    }
}
