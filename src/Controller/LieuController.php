<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route(
    '/lieu',
    name: 'lieu')]
class LieuController extends AbstractController
{
    #[Route(
        '/ajout',
        name: '_ajout')]
    public function ajoutLieu(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response{
        $lieu = new Lieu();
        $lieu->setLongitude(10);
        $lieu->setLatitude(10);
        $lieuForm = $this->createForm(LieuType::class,$lieu);
        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()){

            try{

                $entityManager->persist($lieu);
                $entityManager->flush();

            }catch (\Exception $exception){
                $this->addFlash('danger','Le lieu n\'a pu être ajouté'.$exception->getMessage());
            }
        }
        return $this->render('lieu/modal_form_lieu.html.twig',compact('lieuForm'));
    }
}
