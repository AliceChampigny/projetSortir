<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route(
    '/ville',
    name:'ville')]
class VilleController extends AbstractController{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response : envoie vers le formulaire d'ajout de ville
     * @throws \Exception : si erreur lors de l'entrée en base de données
     */
    #[Route(
        '/ajout',
        name: '_ajoutville'
    )]
    public function ajoutVille(
        Request $request,
        EntityManagerInterface $entityManager

    ): Response{

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class,$ville);
        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()){
            try{

                $entityManager->persist($ville);
                $entityManager->flush();
                return $this->redirectToRoute('admin_gestionVille');

            }catch (\Exception $exception){
                $this->addFlash('danger','L\'ajout de la ville n\'a pas été effectuée'.$exception->getMessage());
            }
        }
        return $this->render('ville/modal_form_ville.html.twig',compact('villeForm'));
    }
}
