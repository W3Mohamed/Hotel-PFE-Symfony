<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HotelController extends AbstractController
{
    #[Route('/index', name: 'accueil')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/chambres', name: 'chambres')]
    public function chambres(): Response
    {
        return $this->render('chambres.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/detail', name: 'detail')]
    public function detail(): Response
    {
        return $this->render('detail.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/panier', name: 'panier')]
    public function panier(): Response
    {
        return $this->render('panier.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/propos', name: 'propos')]
    public function propos(): Response
    {
        return $this->render('propos.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/reservation', name: 'reservation')]
    public function reservation(): Response
    {
        return $this->render('reservation.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }
}
