<?php
namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Créer l'admin
        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Créer des événements de démo
        $events = [
            ['Conférence Tech Sousse 2026', 'Une journée dédiée aux innovations technologiques et aux startups tunisiennes.', '+20 jours', 'Palais des Congrès, Sousse', 200, null],
            ['Workshop Symfony avancé', 'Formation intensive sur Symfony 7, API Platform et les meilleures pratiques.', '+5 jours', 'ISSAT Sousse', 30, null],
            ['Hackathon IA & Data', 'Un hackathon de 48h autour de l\'intelligence artificielle et du Big Data.', '+15 jours', 'Pépinière d\'entreprises, Sousse', 100, null],
            ['Gala annuel FIA3', 'Soirée de remise des diplômes et networking des étudiants en informatique.', '+30 jours', 'Hôtel Riviera, Sousse', 300, null],
        ];

        foreach ($events as [$title, $desc, $days, $location, $seats, $img]) {
            $event = new Event();
            $event->setTitle($title);
            $event->setDescription($desc);
            $date = new \DateTime();
            $date->modify($days . ' days');
            $event->setDate($date);
            $event->setLocation($location);
            $event->setSeats($seats);
            $manager->persist($event);
        }

        $manager->flush();
    }
}