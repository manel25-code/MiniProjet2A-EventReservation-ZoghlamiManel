<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/', name: 'event_list')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAllUpcoming();
        return $this->render('event/index.html.twig', ['events' => $events]);
    }

    #[Route('/event/{id}', name: 'event_show')]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', ['event' => $event]);
    }

    #[Route('/reservation/{id}', name: 'event_reserve', methods: ['GET', 'POST'])]
    public function reserve(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($event->getAvailableSeats() <= 0) {
            $this->addFlash('error', 'Plus de places disponibles pour cet événement.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', '🎉 Réservation confirmée ! Vous recevrez une confirmation par email.');
            return $this->redirectToRoute('reservation_confirmation', ['id' => $reservation->getId()]);
        }

        return $this->render('event/reserve.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation/{id}', name: 'reservation_confirmation')]
    public function confirmation(Reservation $reservation): Response
    {
        return $this->render('event/confirmation.html.twig', ['reservation' => $reservation]);
    }
}