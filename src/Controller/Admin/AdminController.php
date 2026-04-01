<?php
namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EventRepository $eventRepo, ReservationRepository $reservRepo): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'events' => $eventRepo->findAll(),
            'totalReservations' => $reservRepo->count([]),
            'totalEvents' => $eventRepo->count([]),
        ]);
    }

    #[Route('/events', name: 'admin_events')]
    public function events(EventRepository $eventRepo): Response
    {
        return $this->render('admin/events.html.twig', [
            'events' => $eventRepo->findAll(),
        ]);
    }

    #[Route('/event/new', name: 'admin_event_new', methods: ['GET', 'POST'])]
    public function newEvent(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('events_images_directory'), $newFilename);
                    $event->setImage($newFilename);
                } catch (FileException $e) {}
            }
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_form.html.twig', ['form' => $form->createView(), 'event' => $event]);
    }

    #[Route('/event/{id}/edit', name: 'admin_event_edit', methods: ['GET', 'POST'])]
    public function editEvent(Event $event, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $safeFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('events_images_directory'), $safeFilename);
                $event->setImage($safeFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Événement mis à jour !');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('admin/event_form.html.twig', ['form' => $form->createView(), 'event' => $event]);
    }

    #[Route('/event/{id}/delete', name: 'admin_event_delete', methods: ['POST'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé.');
        }
        return $this->redirectToRoute('admin_events');
    }

    #[Route('/event/{id}/reservations', name: 'admin_event_reservations')]
    public function eventReservations(Event $event): Response
    {
        return $this->render('admin/reservations.html.twig', ['event' => $event]);
    }
}