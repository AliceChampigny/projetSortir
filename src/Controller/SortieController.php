<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/ajouter',
        name: 'ajouter')]
    public function ajouterunesortie(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {

        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();
//
            return $this->redirectToRoute('main_accueil');
        }

        return $this->render('sortie/ajouterunesortie.html.twig',
            compact('sortieForm'));
    }
}
