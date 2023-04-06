<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController{
    #[Route(
        '/',
        name: 'main_accueil')]
    public function index(

    ): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('sortie_liste');
        }
        else{
            $sorties = null;
            return $this->render('main/accueil.html.twig',compact('sorties'));
        }

    }
}
