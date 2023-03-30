<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieFormType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: '_liste')]
    public function ListeSorties(
        SortieRepository $sortieRepository
    ): Response
    {
        $sorties = $sortieRepository->findAll();


        return $this->render('main/accueil.html.twig',
            compact('sorties')
        );
    }

//----------------------------------------------------------------------------------------------------------------------
    #[Route('/ajouter/{organisateur}',
        name: '_ajouter')]
    public function ajouterunesortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        Participant            $organisateur,

    ): Response
    {

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                $ville = new Ville();
                $ville->setNom('Nantes');
                $ville->setCodePostal('44300');

                $ville->addLieux($sortie->getLieu());

                $etat = new Etat();
                $etat->setLibelle('Créée');


                $sortie->setOrganisateur($organisateur);
                $sortie->setCampus($organisateur->getCampus());


                $entityManager->persist($ville);
                $entityManager->persist($etat);

                $sortie->setEtat($etat);


                $entityManager->persist($sortie);
                $entityManager->flush();


                $this->addFlash('success', "Votre sortie a été ajoutée");
                return $this->redirectToRoute('sortie_liste');

            } catch (\Exception $exception) {
                $this->addFlash('danger', "Votre sortie n'a pas été ajoutée" . $exception->getMessage());
                return $this->redirectToRoute('sortie_ajouter', [
                    'organisateur' => $organisateur->getId()
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
        if ($sortie) {
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
        return $this->redirectToRoute('main_accueil');
    }

//------------------------------------------------------------------------------------------//

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
//----------------------------------------------------------------------------------//
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
        return $this->redirectToRoute('main_accueil');
    }
    //---------------------------------------------------------------------------------//
//    #[Route('/cloture/{sortie}',
//    requirements: 'cloture')]
//
//    public function cloturerSortie(
//        EntityManagerInterface $entityManager,
//        Sortie                 $sortie,
//    ): Response
//    {
//
//        $sortie = $entityManager->getRepository(Sortie::class)->find($Id);
//        $nbParticipants = $sortie->getNbParticipants();
//        $nbMaxInscriptions = $sortie->getNbMaxInscriptions();
//        $dateLimiteInscription = $sortie->getDateLimiteInscription();
//
//        if ($nbParticipants >= $nbMaxInscriptions) {
//            $sortie->setEtat(Sortie::Cloturee);
//            $entityManager->flush();
//        }
//
//        $aujourdhui = new \DateTime();
//        if ($aujourdhui > $dateLimiteInscription) {
//           $sortie->setEtat(Sortie::Cloturee);
//           $entityManager->flush();
//        }
//
//        return $this->redirectToRoute('main_accueil');
//    }

}
