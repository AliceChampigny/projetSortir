<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/participant',
    name:'participant' )]
class ParticipantController extends AbstractController{
    #[Route(
        '',
        name: '_accueilcnte')]
    public function accueilcnte(

    ): Response{
        return $this->redirectToRoute('main_accueil');

    }
}
