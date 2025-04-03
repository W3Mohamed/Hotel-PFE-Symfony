<?php

namespace App\Controller;

use App\Entity\Chambres;
use App\Entity\Panier;
use App\Entity\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function chambres(Request $request,EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $reservationData = $session->get('reservation_data');

        $chambres = $entityManager->getRepository(Chambres::class)->findAll();
        return $this->render('chambres.html.twig', [
            'chambres' => $chambres,
            'reservation_data' => $reservationData
        ]);
    }

    #[Route('/store-dates', name: 'store_dates')]
    public function storeDates(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        
        // Valider les dates
        $checkin = new \DateTime($request->query->get('checkin'));
        $checkout = new \DateTime($request->query->get('checkout'));
        
        if ($checkin >= $checkout) {
            $this->addFlash('error', 'La date de départ doit être après la date d\'arrivée');
            return $this->redirectToRoute('accueil');
        }

        // Stocker en session
        $session->set('reservation_data', [
            'dateArrive' => $checkin,
            'dateDepart' => $checkout,
            'nbAdulte' => (int)$request->query->get('adults'),
            'nbEnfant' => (int)$request->query->get('children')
        ]);

        return $this->redirectToRoute('chambres');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/detail/{id}', methods:['GET'], name: 'detail')]
    public function detail(Request $request, EntityManagerInterface $entityManager, Chambres $chambre): Response
    {
        $session = $request->getSession();
        $reservationData = $session->get('reservation_data');
        
        $services = $entityManager->getRepository(Services::class)->findAll();
        return $this->render('detail.html.twig', [
            'chambre' => $chambre,
            'services' => $services,
            'reservation_data' => $reservationData
        ]);
    }

    #[Route('/panier', name: 'panier', methods: ['GET'])]
    public function panier(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $sessionId = $session->getId();
         // Pour vérifier que la session fonctionne, stockez quelque chose dedans
        if (!$session->has('test_value')) {
            $session->set('test_value', 'Session test à ' . time());
        }
        
        // Debug vérification
        // dd([
        //     'session_id' => $sessionId,
        //     'is_started' => $session->isStarted(),
        //     'test_value' => $session->get('test_value'),
        //     'all_data' => $session->all(),
        //     'cookie_params' => session_get_cookie_params()
        // ]);

        $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId]);
 
        // Si le panier n'existe pas, redirige directement avec un panier vide
        if (!$panier) {
            return $this->render('panier.html.twig', [
                'chambres' => [],
            ]);
        }
        $panierChambres = $panier->getPanierChambres();
        // Si le panier est vide, on affiche un panier vide
        if ($panierChambres->isEmpty()) {
            return $this->render('panier.html.twig', [
                'chambres' => [],
            ]);
        }
        $nbNuit = $panier->getDateArrive()->diff($panier->getDateDepart())->days;
        $totalServices = 0;
        $total = 0;
        foreach($panierChambres as $panierChambre){
            $prixChambre = $panierChambre->getChambre()->getPrix() * $nbNuit;
            $totalServicesChambre = 0;
            foreach ($panierChambre->getPanierServices() as $panierService) {
                $totalServicesChambre += $panierService->getServiceId()->getPrix() * $nbNuit;
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
            'nbNuit' => $nbNuit,
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
