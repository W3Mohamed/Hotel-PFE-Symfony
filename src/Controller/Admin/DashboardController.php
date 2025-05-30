<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Chambres;
use App\Entity\Contact;
use App\Entity\Event;
use App\Entity\Faq;
use App\Entity\Reservations;
use App\Entity\Services;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ChambresCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin Hotel');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de Bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Chambres', 'fas fa-bed', Chambres::class);
        yield MenuItem::linkToCrud('Services', 'fas fa-concierge-bell', Services::class);
        yield MenuItem::linkToCrud('Reservations', 'fas fa-receipt', Reservations::class);
        yield MenuItem::linkToCrud('Événements', 'fas fa-calendar-alt', Event::class);
        yield MenuItem::linkToCrud('Messages de contact', 'fas fa-envelope', Contact::class);

        yield MenuItem::section('ChatBot');
        yield MenuItem::linkToCrud('Categorie', 'fas fa-envelope', Category::class);
        yield MenuItem::linkToCrud('F.A.Q', 'fas fa-envelope', Faq::class);
        // Ajouter un lien vers la création de réservation manuelle
    //     yield MenuItem::section('Outils');
    //     yield MenuItem::linkToRoute('Nouvelle réservation', 'fas fa-calendar-plus', 'admin_reservation_new');
    //     yield MenuItem::linkToRoute('Gestion des réservations', 'fas fa-list-alt', 'admin_reservations_list');
    }
}