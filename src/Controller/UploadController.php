<?php

namespace App\Controller;
use App\Entity\Participant;
use App\Repository\CampusRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\FileUploadType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route(
        '/test-upload',
        name: 'app_test_upload')]
    public function excelCommunesAction(
        Request                     $request,
        FileUploader                $file_uploader,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        CampusRepository            $campusRepository
    )
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('upload_file')->getData();
            if ($file) {
                $file_name = $file_uploader->upload($file);
                if ($file_name !== null) {
                    try{
                    $directory = $file_uploader->getTargetDirectory();
                    $full_path = $directory . '/' . $file_name;

                    $file = fopen($full_path, "r");
                    fgets($file);
                    while (($line = fgetcsv($file, 10000, ';')) !== FALSE) {

                        $user = new Participant();
                        $user->setNom($line[0]);
                        $user->setPrenom($line[1]);
                        $user->setPseudo($line[2]);
                        $user->setEmail($line[3]);
                        $user->setTelephone($line[4]);
                        $user->setPassword($userPasswordHasher->hashPassword(
                            $user,
                            $line[5]));
                        $campus = $campusRepository->find($line[6]);
                        $user->setCampus($campus);
                        $user->setActif(true);
                        $user->setAdministrateur(false);

                        $entityManager->persist($user);
                    }
                    fclose($file);
                    $entityManager->flush();
                    $this->addFlash('danger','Les données transmises via le fichier csv ont bien été entrées en base de données. ');
                    return $this->redirectToRoute('admin_gestionutilisateur');
                }catch (\Exception $exception){
                        $this->addFlash('danger','Impossible de créer de nouveau utilisateur à partir du fichier csv'.$exception->getMessage());
                        return $this->redirectToRoute('admin_gestionutilisateur');
                    }
                }
            }
        }
        return $this->render('app/test-upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}



