<?php

namespace App\Controller;
use App\Repository\ActiviteRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Entity\Reservation;
use DateTime;
use App\Form\ReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(ReservationRepository $rr): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $rr->findBy(['user' => 1]),
        ]);
    }
    #[Route('/reservationback', name: 'app_reservationback')]
    public function index2(ReservationRepository $rr): Response
    {
        return $this->render('reservation/list.html.twig', [
            'reservations' => $rr->findAll(),
        ]);
    }
    #[Route('/addreservation/{idactivite}', name: 'add_reservation')]
    public function add(Request $request,UserRepository $userRepository,$idactivite,ActiviteRepository $activiteRepository): Response
    {
        $reser = new Reservation();
        $user = $userRepository->find(1);
        $activite = $activiteRepository->find($idactivite); 
        $reser->setUser($user); 
        $reser->setStatus("en cours");
        $reser->setDate(new DateTime('now'));
        $reser->setActivite($activite);
        $form = $this->createForm(ReservationType::class, $reser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager = $this->getDoctrine()->getManager();
            $reser->setTotal($activite->getPrix()* $reser->getNombrePersonne());
            $entityManager->persist($reser);
            $entityManager->flush();

                return $this->redirectToRoute('app_reservation'); 
        } else {
            $this->addFlash('error', 'Il n\'y a pas assez de places disponibles pour cette activité.');
        

    }
        return $this->render('reservation/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/confirm-reservation/{id}', name: 'confirm_reservation')]
    public function confirmReservation($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Pas de réservation trouvée pour l\'id '.$id);
        }

        $reservation->setStatus('confirmé');
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a été confirmée.');
        return $this->redirectToRoute('app_reservationback'); 
    }
    #[Route('/refuse-reservation/{id}', name: 'refuse_reservation')]
    public function refuseReservation($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Pas de réservation trouvée pour l\'id '.$id);
        }
        $reservation->setStatus('annulé');
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a été refusée.');
        return $this->redirectToRoute('app_reservationback'); 
    }
}
