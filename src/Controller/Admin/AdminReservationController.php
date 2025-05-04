<?php
// src/Controller/Admin/AdminReservationController.php
namespace App\Controller\Admin;

use App\Entity\{Chambres, Panier, PanierChambres, PanierService, Reservations, Services, User};
use App\Repository\{ChambresRepository, ServicesRepository, UserRepository};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminReservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChambresRepository $chambresRepo,
        private ServicesRepository $servicesRepo,
        private UserRepository $userRepo
    ) {}

    #[Route('/admin/reservation/new', name: 'admin_reservation_new')]
    public function new(Request $request): Response
    {
        // Récupérer ou créer un panier admin
        $panier = $this->getOrCreateAdminPanier();
        
        // Récupérer tous les services pour les afficher dans le formulaire
        $services = $this->servicesRepo->findAll();
        
        // Initialiser les données de réservation
        $reservationData = $this->handleReservationDates($request, $panier);
        
        // Vérifier la disponibilité si dates fournies
        $chambresDisponibles = [];
        $errors = [];
        
        if ($reservationData['dateArrive'] && $reservationData['dateDepart']) {
            if ($reservationData['dateArrive'] >= $reservationData['dateDepart']) {
                $errors[] = 'La date de départ doit être après la date d\'arrivée';
            } else {
                $reservedCounts = $this->chambresRepo->findReservedCountsForDates(
                    $reservationData['dateArrive'], 
                    $reservationData['dateDepart']
                );
                $chambresDisponibles = $this->chambresRepo->findAvailableRooms($reservedCounts);
                
                if (empty($chambresDisponibles)) {
                    $errors[] = 'Aucune chambre disponible pour ces dates';
                }
            }
        }

        // Calculer le prix total actuel du panier
        $prixTotal = $this->calculatePanierTotal($panier);
        $nombreNuits = $reservationData['dateArrive'] && $reservationData['dateDepart'] 
            ? $reservationData['dateArrive']->diff($reservationData['dateDepart'])->days 
            : 0;

        return $this->render('admin/reservation/new.html.twig', [
            'chambres' => $chambresDisponibles,
            'reservation_data' => $reservationData,
            'panier' => $panier,
            'services' => $services,
            'errors' => $errors,
            'prix_total' => $prixTotal,
            'nombre_nuits' => $nombreNuits
        ]);
    }

    #[Route('/admin/reservation/add-chambre', name: 'admin_reservation_add_chambre', methods: ['POST'])]
    public function addChambre(Request $request): Response
    {
        $panier = $this->getOrCreateAdminPanier();

        // Récupérer les données du formulaire
        $chambreId = $request->request->get('chambre_id');
        $dateArrive = new \DateTime($request->request->get('dateArrive'));
        $dateDepart = new \DateTime($request->request->get('dateDepart'));
        $servicesIds = $request->request->all('services', []);

        // Mettre à jour les dates du panier si nécessaire
        $panier->setDateArrive($dateArrive);
        $panier->setDateDepart($dateDepart);
        $panier->setNbAdulte((int)$request->request->get('nbAdulte', 1));
        $panier->setNbEnfant((int)$request->request->get('nbEnfant', 0));

        // Vérifier la chambre
        $chambre = $this->em->getRepository(Chambres::class)->find($chambreId);
        if (!$chambre) {
            $this->addFlash('error', 'Chambre non trouvée');
            return $this->redirectToRoute('admin_reservation_new');
        }

        // Vérifier disponibilité
        if (!$this->isChambreDisponible($chambre, $dateArrive, $dateDepart)) {
            $this->addFlash('error', 'Chambre non disponible pour ces dates');
            return $this->redirectToRoute('admin_reservation_new');
        }

        // Vérifier si la chambre est déjà dans le panier
        foreach ($panier->getPanierChambres() as $existingChambre) {
            if ($existingChambre->getChambre()->getId() === $chambre->getId()) {
                $this->addFlash('warning', 'Cette chambre est déjà dans votre panier');
                return $this->redirectToRoute('admin_reservation_new');
            }
        }

        // Ajouter la chambre au panier
        $panierChambre = new PanierChambres();
        $panierChambre->setPanier($panier);
        $panierChambre->setChambre($chambre);
        $this->em->persist($panierChambre);

        // Ajouter les services
        foreach ($servicesIds as $serviceId) {
            $service = $this->em->getRepository(Services::class)->find($serviceId);
            if ($service) {
                $panierService = new PanierService();
                $panierService->setPanierChambre($panierChambre);
                $panierService->setService($service);
                $this->em->persist($panierService);
            }
        }

        $this->em->flush();
        $this->addFlash('success', 'Chambre ajoutée au panier');

        return $this->redirectToRoute('admin_reservation_new');
    }

    #[Route('/admin/reservation/remove-chambre/{id}', name: 'admin_reservation_remove_chambre')]
    public function removeChambre(PanierChambres $panierChambre): Response
    {
        $panier = $this->getOrCreateAdminPanier();
        
        // Vérifier que cette chambre appartient bien au panier admin
        if ($panierChambre->getPanier()->getId() === $panier->getId()) {
            $this->em->remove($panierChambre);
            $this->em->flush();
            $this->addFlash('success', 'Chambre retirée du panier');
        } else {
            $this->addFlash('error', 'Impossible de retirer cette chambre');
        }
        
        return $this->redirectToRoute('admin_reservation_new');
    }

    #[Route('/admin/reservation/confirm', name: 'admin_reservation_confirm', methods: ['POST'])]
    public function confirmReservation(Request $request): Response
    {
        $panier = $this->getOrCreateAdminPanier();

        if ($panier->getPanierChambres()->isEmpty()) {
            $this->addFlash('error', 'Le panier est vide');
            return $this->redirectToRoute('admin_reservation_new');
        }

        // Créer le client à partir des données du formulaire
        $client = new User();
        $client->setNom($request->request->get('nom'));
        $client->setPrenom($request->request->get('prenom'));
        $client->setEmail($request->request->get('email'));
        $client->setTelephone($request->request->get('telephone'));
        $client->setAdresse($request->request->get('adresse'));
        $client->setVille($request->request->get('ville'));
        $client->setCodePostale($request->request->get('code_postal'));
        $client->setPays($request->request->get('pays'));

        $this->em->persist($client);

        // Créer la réservation
        $reservation = new Reservations();
        $reservation->setUser($client);
        $reservation->setPanier($panier);
        $reservation->setStatus('Confirmée');
        $reservation->setPrixTotal($this->calculatePanierTotal($panier));
        $reservation->setDateCreation(new \DateTimeImmutable());
        $reservation->setToken(bin2hex(random_bytes(16)));
        $reservation->setCommentaire($request->request->get('commentaire', ''));

        // Marquer le panier comme complété
        $panier->setStatus(true);

        $this->em->persist($reservation);
        $this->em->flush();

        $this->addFlash('success', 'Réservation #' . $reservation->getId() . ' créée avec succès');
        return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
    }

    #[Route('/admin/reservation/{id}', name: 'admin_reservation_show')]
    public function show(Reservations $reservation): Response
    {
        // Calcul du nombre de nuits
        $nombreNuits = $reservation->getPanier()->getDateArrive()->diff(
            $reservation->getPanier()->getDateDepart()
        )->days;
        
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
            'nombre_nuits' => $nombreNuits
        ]);
    }
    
    private function getOrCreateAdminPanier(): Panier
    {
        // Dans un environnement multi-admin, il faudrait utiliser l'ID admin
        // $user = $this->getUser();
        // $sessionId = 'admin_' . $user->getId();
        $sessionId = 'admin'; 

        $panier = $this->em->getRepository(Panier::class)->findOneBy([
            'session_id' => $sessionId,
            'status' => false
        ]);

        if (!$panier) {
            $panier = new Panier();
            $panier->setSessionId($sessionId);
            $panier->setDateCreation(new \DateTime());
            $panier->setStatus(false);
            $panier->setNbAdulte(1);
            $panier->setNbEnfant(0);
            // Initialiser des dates par défaut
            $panier->setDateArrive(new \DateTime());
            $panier->setDateDepart((new \DateTime())->modify('+1 day'));
            
            $this->em->persist($panier);
            $this->em->flush();
        }

        return $panier;
    }

    private function handleReservationDates(Request $request, Panier $panier): array
    {
        $data = [
            'dateArrive' => $panier->getDateArrive(),
            'dateDepart' => $panier->getDateDepart(),
            'nbAdulte' => $panier->getNbAdulte(),
            'nbEnfant' => $panier->getNbEnfant()
        ];

        if ($request->query->has('dateArrive') && $request->query->has('dateDepart')) {
            $dateArrive = new \DateTime($request->query->get('dateArrive'));
            $dateDepart = new \DateTime($request->query->get('dateDepart'));
            
            if ($dateArrive < $dateDepart) {
                $panier->setDateArrive($dateArrive);
                $panier->setDateDepart($dateDepart);
                $panier->setNbAdulte((int)$request->query->get('adults', 1));
                $panier->setNbEnfant((int)$request->query->get('children', 0));
                
                $this->em->flush();
                
                $data = [
                    'dateArrive' => $dateArrive,
                    'dateDepart' => $dateDepart,
                    'nbAdulte' => (int)$request->query->get('adults', 1),
                    'nbEnfant' => (int)$request->query->get('children', 0)
                ];
            }
        }

        return $data;
    }

    private function isChambreDisponible(Chambres $chambre, \DateTimeInterface $dateArrive, \DateTimeInterface $dateDepart): bool
    {
        $reservedCounts = $this->chambresRepo->findReservedCountsForDates($dateArrive, $dateDepart);
        $reserved = $reservedCounts[$chambre->getId()] ?? 0;
        return $reserved < $chambre->getNombre();
    }

    private function calculatePanierTotal(Panier $panier): float
    {
        $total = 0;
        
        // Vérifier si les dates sont définies
        if (!$panier->getDateArrive() || !$panier->getDateDepart()) {
            return $total;
        }
        
        // Calculer le nombre de nuits
        $interval = $panier->getDateArrive()->diff($panier->getDateDepart());
        $nights = $interval->days;
        
        // Si pas de nuits, pas de calcul
        if ($nights <= 0) {
            return $total;
        }

        foreach ($panier->getPanierChambres() as $panierChambre) {
            // Prix de la chambre
            $total += $panierChambre->getChambre()->getPrix() * $nights;
            
            // Prix des services (aussi multiplié par le nombre de nuits)
            foreach ($panierChambre->getPanierServices() as $panierService) {
                $total += $panierService->getService()->getPrix() * $nights;
            }
        }

        return $total;
    }
}