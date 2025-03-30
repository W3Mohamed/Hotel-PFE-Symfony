<?php

namespace App\Controller;

use App\Entity\Chambres;
use App\Entity\Panier;
use App\Entity\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HotelController extends AbstractController
{
    #[Route('/index', name: 'accueil')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $chambres = $entityManager->getRepository(Chambres::class)->findBy([], ['id' => 'DESC'], 6);
        $services = $entityManager->getRepository(Services::class)->findAll();
        return $this->render('index.html.twig', [
            'chambres' => $chambres,
            'services' => $services,
        ]);
    }

    #[Route('/chambres', name: 'chambres')]
    public function chambres(EntityManagerInterface $entityManager): Response
    {
        $chambres = $entityManager->getRepository(Chambres::class)->findAll();
        return $this->render('chambres.html.twig', [
            'chambres' => $chambres,
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/detail/{id}', methods:['GET'], name: 'detail')]
    public function detail(EntityManagerInterface $entityManager,Chambres $chambre): Response
    {
        $services = $entityManager->getRepository(Services::class)->findAll();
        return $this->render('detail.html.twig', [
            'chambre' => $chambre,
            'services' => $services,
        ]);
    }

    #[Route('/panier', name: 'panier')]
    public function panier(SessionInterface $session, EntityManagerInterface $em): Response
    {
        // $session->start();
        $sessionId = $session->getId();
        dd($sessionId);
        $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId]);
 
        // Si le panier n'existe pas, redirige directement avec un panier vide
        if (!$panier) {
            return $this->render('panier.html.twig', [
                'chambres' => [],
            ]);
        }
        $panierChambres = $panier->getPanierChambres();
        // Si le panier est vide, on affiche un panier vide
        if (empty($panierChambres)) {
            return $this->render('panier.html.twig', [
                'chambres' => [],
            ]);
        }
        $totalServices = 0;
        $total = 0;
        foreach($panierChambres as $panierChambre){
            $prixChambre = $panierChambre->getChambre()->getPrix() * $panierChambre->getNbNuit();
            $totalServicesChambre = 0;
            foreach ($panierChambre->getPanierServices() as $panierService) {
                $totalServicesChambre += $panierService->getServiceId()->getPrix() * $panierChambre->getNbNuit();
            }
            $prixTotalChambre = $prixChambre + $totalServicesChambre; 
            $totalServices += $totalServicesChambre;
            $total += $prixTotalChambre;

            $panierChambre->prixChambre = $prixChambre;
            $panierChambre->prixTotalChambre = $prixTotalChambre;
        }
        $panierChambre->totalServices = $totalServices;
        $panierChambre->total = $total;

        return $this->render('panier.html.twig', [
            'chambres' => $panierChambres, // Liste des chambres ajoutées
            'prixTotalChambre' => $prixTotalChambre,
            'prixChambre' => $prixChambre,
            'totalServices' => $totalServices,
            'total' => $total,
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
