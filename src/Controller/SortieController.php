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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie',name:'sortie')]
class SortieController extends AbstractController{

    #[Route('/', name: '_liste')]
    public function ListeSorties(
        SortieRepository $sortieRepository,
        Request $request,
        FormFactoryInterface $formFactory,
        EtatRepository $etatRepository,

    ): Response
    {
        try {
            $filter = new Filter();
            $form = $formFactory->create(FilterType::class, $filter);
            $form->handleRequest($request);
            $userConnecte = $this->getUser();
            $id = 5;
            $sortiesPassees = $etatRepository->find($id);
//         return $this->redirectToRoute('sortie_liste');
        } catch (\Exception $exception) {
            $this->addFlash('danger', "Impossible d'afficher les sorties damandées" . $exception->getMessage());
        }
            return $this->render('main/accueil.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sortieRepository -> filtreListeSorties($filter, $userConnecte, $sortiesPassees),

            ]
        );
    }


//----------------------------------------------------------------------------------------------------------------------
    #[Route('/ajouter/{organisateur}',
        name: '_ajouter')]
    public function ajouterunesortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        Participant $organisateur,
        EtatRepository $etatRepository

    ): Response
    {

        $sortie = new Sortie();
        $sortie->setDateHeureDebut(new \DateTime('+ 2 days'));
        $sortie->setDateLimiteInscription(new \DateTime('+1 day'));
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                $etat = $etatRepository->findOneBy(['id'=>1]); //id de l'état Créée
                $sortie->setCampus($organisateur->getCampus());
                $etat->addSorty($sortie);
                $organisateur->addSortiesOrganisee($sortie);

                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', "Votre sortie a été ajoutée" );
                return $this->redirectToRoute('sortie_liste');
            } catch (\Exception $exception){
                $this->addFlash('danger', "Votre sortie n'a pas été ajoutée". $exception->getMessage() );
                return $this->redirectToRoute('sortie_ajouter',[
                    'organisateur'=>$organisateur->getId()
                ]);
            }
        }
        return $this->render('sortie/ajouterunesortie.html.twig',
            compact('sortieForm'));
    }

//----------------------------------------------------------------------------------------------------------------------

    #[Route('/inscription/{sortie}',
        name: '_inscription')]
    public function inscriptionsorties(

        EntityManagerInterface $entityManager,
        Sortie                 $sortie,
    ): Response
    {
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

    #[Route('/sortie/{id}', name: '_affichersortie',
        requirements: ['id' => '\d+'])]
    public function afficherSortie(
        sortie           $id,
        SortieRepository $sortieRepository
    ): Response
    {
        $sortie = $sortieRepository->findOneby(['id' => $id]);

        return $this->render('sortie/affichersortie.html.twig',
            compact('sortie')
        );
    }
//----------------------------------------------------------------------------------------------------------------------
    #[Route('/desistement/{sortie}',
        name: '_desistement')]
    public function desistementSorties(

        EntityManagerInterface $entityManager,
        Sortie                 $sortie,
    ): Response
    {
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
    #[Route(
        '/modifier/{sortie}',
        name: '_modifiersortie')]
    public function modifierSortie(
        EntityManagerInterface $entityManager,
        Request                $request,
        Sortie                 $sortie,
        VilleRepository        $villeRepository
    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser()){
            if($sortie->getEtat()->getId()===1 ){
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
                        $this->addFlash('danger', "Les modifications n'ont pas été effectuées". $exception->getMessage() );
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
    #[Route(
        '/supprimer/{sortie}',
        name: '_supprimersortie')]
    public function supprimerSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager
    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser()){
            if($sortie->getEtat()->getId()===1 ){
                try{

                    $entityManager->remove($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre sortie a bien été supprimée" );
                    return $this->redirectToRoute('sortie_liste');

                }catch (\Exception $exception) {
                    $this->addFlash('danger', "La suppression n'a pas été effectuée" . $exception->getMessage());
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
    #[Route(
        '/annuler/{sortie}',
        name: '_annulersortie')]
    public function annulerSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        Request $request,
        MailerInterface $mailer
    ) : Response{

        if($sortie->getOrganisateur() === $this->getUser()){
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
                            
                            L\'organisateur de la sortie '.$sortie->getNom().' vient juste de l\'annuler. Le motif d\'annulation est disponible sur notre au site au niveau du détails de la sortie.
                            Nous nous excusons pour la gêne occasionnée.
                             
                            Au revoir et à bientôt sur les SortiesEnitiennes.com !');
                        $mailer->send($email);

                        return $this->redirectToRoute('sortie_liste');

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
    #[Route(
        '/publier/{sortie}',
        name: '_publiersortie')]
    public function publierSortie(
        Sortie $sortie,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ):Response{

        if($sortie->getOrganisateur() === $this->getUser()){
            if($sortie->getEtat()->getId()===1 ){

                try{
                    $etat = $etatRepository->findOneBy(['id' => 2]);
                    $sortie->setEtat($etat);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre sortie a bien été publiée");
                    $this->redirectToRoute('sortie_liste');
                }catch (\Exception $exception){
                    $this->addFlash('danger', "La publication n'a pu être effectuée". $exception->getMessage() );
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
                                                    dont vous n\'êtes pas l\'organisateur est impossible' );
        }
        return $this->redirectToRoute('sortie_liste');
    }
}
