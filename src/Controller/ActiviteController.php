<?php

namespace App\Controller;
use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


class ActiviteController extends AbstractController
{
    #[Route('/activite', name: 'app_activite')]
    public function index(ActiviteRepository $vr): Response
    {
        return $this->render('activite/index.html.twig', [
            'activites' => $vr->findAll(),
        ]);
    }
    #[Route('/addacti', name: 'addacti')]
    public function add(Request $request): Response
    {

        $ac = new Activite();
        $form = $this->createForm(ActiviteType::class, $ac);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if ($file) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('images_directory'), $fileName);
                $ac->setImage($fileName);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($ac);
                $entityManager->flush();

                return $this->redirectToRoute('activiteback');
            }
        }
        return $this->render('activite/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/activite/delete/{id}', name: 'activitedelete')]
    public function delete(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $activite = $entityManager->getRepository(Activite::class)->find($id);
        $entityManager->remove($activite);
        $entityManager->flush();
        return $this->redirectToRoute('activiteback');
    }
    #[Route('/activite/edit/{id}', name: 'activiteedit')]
    public function edit(Request $request,$id, EntityManagerInterface $entityManager): Response
    {
        $activite = $entityManager->getRepository(Activite::class)->find($id);
        if (!$activite) {
            throw $this->createNotFoundException('No Activite found for id '.$activite->getId());
        }
    
        $originalImage = $activite->getImage();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
    
            if ($file) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                try {
                    $file->move($this->getParameter('images_directory'), $fileName);
                    $activite->setImage($fileName);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'A problem occurred while uploading the file.');
                }
            } else {
                $prop->setImage($originalImage);
            }
    
            $entityManager->persist($activite);
            $entityManager->flush();
    
            $this->addFlash('success', 'Activite mise à jour avec succès.');
            return $this->redirectToRoute('activiteback');
        }
    
        return $this->render('activite/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/activiteback', name: 'activiteback')]
    public function liste(ActiviteRepository $vr): Response
    {
        $activite = $vr->findAll();
        
        return $this->render('activite/list.html.twig', [
            'activites' => $activite,
        ]);
    }
}
