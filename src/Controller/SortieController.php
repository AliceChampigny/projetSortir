<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie',name:'sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: '_liste')]
    public function ListeSorties(
      SortieRepository $sortieRepository
    ): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('main/accueil.html.twig',
            compact( 'sorties')
        );
    }

    #[Route('/ajouter/{organisateur}',
        name: '_ajouter')]

    public function ajouterunesortie(
        Request $request,
        EntityManagerInterface $entityManager,
        Participant $organisateur,

    ): Response {

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


                $this->addFlash('success', "Votre sortie a été ajoutée" );
            return $this->redirectToRoute('participant_accueilcnte');

            } catch (\Exception $exception){
                $this->addFlash('danger', "Votre sortie n'a pas été ajoutée". $exception->getMessage() );
                return $this->redirectToRoute('ajouter',[
                    'organisateur'=>$organisateur->getId()
                ]);
            }

        }

        return $this->render('sortie/ajouterunesortie.html.twig',
            compact('sortieForm'));

    }
}
