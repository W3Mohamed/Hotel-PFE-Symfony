<?php

namespace App\Controller;

use App\Entity\Chambres;
use App\Entity\Contact;
use App\Entity\Panier;
use App\Entity\PanierChambres;
use App\Entity\PanierService;
use App\Entity\Reservations;
use App\Entity\Services;
use App\Entity\User;
use App\Repository\ChambresRepository;
use App\Repository\EventRepository;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
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
    public function chambres(Request $request, EntityManagerInterface $entityManager, ChambresRepository $chambresRepo): Response
    {
        $session = $request->getSession();
        $sessionId = $session->getId();
        $reservationData = $session->get('reservation_data', []);
        
        if ($request->isMethod('GET') && $request->query->get('checkin') && $request->query->get('checkout')) {
            $dateArrive = new \DateTime($request->query->get('checkin'));
            $dateDepart = new \DateTime($request->query->get('checkout'));
            
            if ($dateArrive >= $dateDepart) {
                throw new \Exception("La date de départ doit être après la date d'arrivée");
            }
            
            // Vider le panier existant lorsque les dates changent
            $panier = $entityManager->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId, 'status' => false]);
            
            if ($panier) {
                $entityManager->remove($panier);
                $entityManager->flush();
            }
            
            // Trouver le nombre de réservations pour chaque chambre
            $reservedCounts = $chambresRepo->findReservedCountsForDates($dateArrive, $dateDepart);
    
            $reservationData = [
                'dateArrive' => $dateArrive,
                'dateDepart' => $dateDepart,
                'nbAdulte' => (int)$request->query->get('adults'),
                'nbEnfant' => (int)$request->query->get('children'),
                'reservedCounts' => $reservedCounts // Stocker les comptes de réservations
            ];
            
            $session->set('reservation_data', $reservationData);
        }
    
        $chambres = empty($reservationData)
            ? $chambresRepo->findAll()
            : $chambresRepo->findAvailableRooms($reservationData['reservedCounts'] ?? []);
        
        return $this->render('chambres.html.twig', [
            'chambres' => $chambres,
            'reservation_data' => $reservationData
        ]);
    }

    #[Route('/store-dates', name: 'store_dates')]
    public function storeDates(Request $request, EntityManagerInterface $em, ChambresRepository $chambresRepo): Response
    {
        $session = $request->getSession();
        $sessionId = $session->getId();

        $checkin = new \DateTime($request->query->get('checkin'));
        $checkout = new \DateTime($request->query->get('checkout'));
        
        if ($checkin >= $checkout) {
            $this->addFlash('error', 'La date de départ doit être après la date d\'arrivée');
            return $this->redirectToRoute('accueil');
        }

        // Trouver le nombre de réservations pour chaque chambre
        $reservedCounts = $chambresRepo->findReservedCountsForDates($checkin, $checkout);

        // Vider le panier existant
        $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId, 'status' => false]);
        if ($panier) {
            $em->remove($panier);
            $em->flush();
        }

        // Stocker en session
        $session->set('reservation_data', [
            'dateArrive' => $checkin,
            'dateDepart' => $checkout,
            'nbAdulte' => (int)$request->query->get('adults'),
            'nbEnfant' => (int)$request->query->get('children'),
            'reservedCounts' => $reservedCounts
        ]);

        return $this->redirectToRoute('chambres');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, [
                'label' => 'Nom Complet',
                'attr' => [
                    'class' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[var(--beige)] focus:border-[var(--beige)]'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[var(--beige)] focus:border-[var(--beige)]'
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'class' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[var(--beige)] focus:border-[var(--beige)]'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 5,
                    'class' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[var(--beige)] focus:border-[var(--beige)]'
                ],
                'data' => $request->query->get('message') 
            ])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();
    
            // Ajoutez ce code pour SweetAlert
            $this->addFlash('success', 'Votre message a été envoyé avec succès!');
    
            return $this->redirectToRoute('contact');
        }
    
        return $this->render('contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/detail/{id}', methods:['GET'], name: 'detail')]
    public function detail(Request $request, EntityManagerInterface $entityManager, Chambres $chambre, ChambresRepository $chambresRepo): Response
    {
        $session = $request->getSession();
        $reservationData = $session->get('reservation_data');
        
        $availableCount = $chambre->getNombre(); // Nombre total de cette chambre
        
        if ($reservationData && isset($reservationData['reservedCounts'][$chambre->getId()])) {
            $availableCount -= $reservationData['reservedCounts'][$chambre->getId()];
        }
        
        $isAvailable = $availableCount > 0;
        
        $services = $entityManager->getRepository(Services::class)->findAll();
        return $this->render('detail.html.twig', [
            'chambre' => $chambre,
            'services' => $services,
            'reservation_data' => $reservationData,
            'is_available' => $isAvailable,
            'available_count' => $availableCount
        ]);
    }

    #[Route('/panier', name: 'panier', methods: ['GET'])]
    public function panier(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $sessionId = $session->getId();
        $reservationData = $session->get('reservation_data');
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
        $paniers = $em->getRepository(Panier::class)->findBy(['session_id' => $sessionId]);
        // Si le panier n'existe pas, redirige directement avec un panier vide
        if (!$paniers) {
            return $this->render('panier.html.twig', [
                'chambres' => [],
                'reservation_data' => $reservationData
            ]);
        }
        foreach ($paniers as $panier) {
            if ($panier->isStatus() === false){
                $panierChambres = $panier->getPanierChambres();
                // Si le panier est vide, on affiche un panier vide
                if ($panierChambres->isEmpty()) {
                    return $this->render('panier.html.twig', [
                        'chambres' => [],
                        'reservation_data' => $reservationData
                    ]);
                }
                $nbNuit = $panier->getDateArrive()->diff($panier->getDateDepart())->days;
                $totalServices = 0;
                $total = 0;
                foreach($panierChambres as $panierChambre){
                    $prixChambre = $panierChambre->getChambre()->getPrix() * $nbNuit;
                    $totalServicesChambre = 0;
                    foreach ($panierChambre->getPanierServices() as $panierService) {
                        $totalServicesChambre += $panierService->getService()->getPrix() * $nbNuit;
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
                    'reservation_data' => $reservationData
                ]);
            }
        }

        return $this->render('panier.html.twig', [
            'chambres' => [],
            'reservation_data' => $reservationData,
            'panier_status' => true,
            'message' => 'Votre réservation est en cours de traitement.'
        ]);

    }

    #[Route('/propos', name: 'propos')]
    public function propos(): Response
    {
        return $this->render('propos.html.twig', [
            'controller_name' => 'HotelController',
        ]);
    }

    #[Route('/evenements', name: 'app_events')]
    public function event(EventRepository $eventRepository): Response
    {
        $featuredEvents = $eventRepository->findBy(['isFeatured' => true], ['date' => 'ASC']);
        $events = $eventRepository->findBy(['isFeatured' => false], ['date' => 'ASC']);

        return $this->render('event.html.twig', [
            'featuredEvents' => $featuredEvents,
            'events' => $events,
        ]);
    }

    #[Route('/reservation', name: 'reservation')]
    public function reservation(Request $request): Response
    {
        $total = (float) $request->request->get('total');
        $paymentMethod = $request->request->get('payment', 'card');
        return $this->render('reservation.html.twig', [
            'payment_method' => $paymentMethod,
            'total' => $total
        ]);
    }

    #[Route('/reservation/confirmer', name: 'ajout_reservation', methods:['POST'])]
    public function ajout_reservation(Request $request, EntityManagerInterface $em, SessionInterface $session,MailerInterface $mailer,LoggerInterface $logger): Response
    {
             // 1. Créer un nouvel utilisateur avec les données du formulaire
            $user = new User();
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setTelephone($request->request->get('telephone'));
            $user->setAdresse($request->request->get('adresse'));
            $user->setVille($request->request->get('ville'));
            $user->setCodePostale($request->request->get('codePostal'));
            $user->setPays($request->request->get('pays'));

            $em->persist($user);

            // 2. Récupérer le panier actuel depuis la session
            $sessionId = $session->getId();
            $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId, 'status' => false]);

            if (!$panier) {
                // Gérer le cas où aucun panier n'est trouvé
                throw $this->createNotFoundException('Aucun panier actif trouvé');
            }

            // 3. Mettre à jour le statut du panier à true
            $panier->setStatus(true);
            $em->persist($panier);

            $token = bin2hex(random_bytes(16));

            // 4. Créer une nouvelle réservation
            $reservation = new Reservations();
            $reservation->setStatus('Confirmée'); 
            $reservation->setPrixTotal((float) $request->request->get('total')); 
            $reservation->setDateCreation(new \DateTimeImmutable());
            $reservation->setCommentaire($request->request->get('commentaires'));
            $reservation->setToken($token);
            $reservation->setUser($user);
            $reservation->setPanier($panier);
            
            $em->persist($reservation);
            
            // 5. Lier la réservation à l'utilisateur
            $user->setReservations($reservation);
            
            // 6. Exécuter toutes les opérations en base de données
            $em->flush();

            try {
                // Créer manuellement le transport SMTP
                $dsn = 'smtp://mohamedbenachenhou430@gmail.com:fcunsvkgyjpcsqhz@smtp.gmail.com:587';
                $transport = Transport::fromDsn($dsn);
                
                // Créer manuellement l'objet Mailer
                $mailer = new Mailer($transport);
                
                $panierChambres = $em->getRepository(PanierChambres::class)->findBy(['panier' => $panier]);
                
                $panierServices = [];
                foreach ($panierChambres as $panierChambre) {
                    foreach ($panierChambre->getPanierServices() as $service) {
                        $panierServices[] = $service;
                    }
                }
                $siteUrl = $this->getParameter('site_url');
                $htmlContent = $this->renderView('emails/reservation_confirmation.html.twig', [
                    'user' => $user,
                    'reservation' => $reservation,
                    'panier' => $panier,
                    'panierChambres' => $panierChambres,
                    'panierServices' => $panierServices,
                    'site_url' => $siteUrl
                ]);

                $email = (new Email())
                    ->from(new Address('mohamedbenachenhou430@gmail.com', 'Hôtel Roxal'))
                    ->to(new Address($user->getEmail(), $user->getPrenom().' '.$user->getNom()))
                    ->subject('Confirmation de réservation #'.$reservation->getId())
                    ->html($htmlContent);
        
                $mailer->send($email);
                $this->addFlash('success', 'Votre réservation a été enregistrée et un email de confirmation a été envoyé.');
        
            } catch (\Throwable $e) {
                $logger->error('Erreur envoi email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'reservation_id' => $reservation->getId()
                ]);
                $this->addFlash('warning', 'Votre réservation a été enregistrée mais l\'email de confirmation n\'a pas pu être envoyé.');
            }

            // Rediriger vers une page de confirmation ou autre
            return $this->redirectToRoute('panier');

    }

    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        try {
            // 1. Créer manuellement le transport SMTP
            $dsn = 'smtp://mohamedbenachenhou430@gmail.com:fcunsvkgyjpcsqhz@smtp.gmail.com:587';

            $transport = Transport::fromDsn($dsn);
    
            // 2. Créer manuellement l'objet Mailer
            $mailer = new Mailer($transport);
    
            // 3. Créer le mail
            $email = (new Email())
                ->from('mohamedbenachenhou430@gmail.com')
                ->to('medmohdz181@gmail.com')
                ->subject('Test envoi manuel SMTP')
                ->text('Ceci est un test envoyé sans injection de dépendance.');
    
            // 4. Envoyer le mail
            $mailer->send($email);
    
            return new Response('Mail envoyé manuellement avec succès ✅');
        } catch (\Throwable $th) {
            return new Response('Erreur : ' . $th->getMessage());
        }


    }

    #[Route('/annuler/{id}/{token}', name: 'annuler_reservation')]
    public function annulerReservation(EntityManagerInterface $em,int $id, string $token): Response
    {

        $reservation = $em->getRepository(Reservations::class)->find($id);

        if (!$reservation || $reservation->getToken() !== $token) {
            throw $this->createNotFoundException('Réservation non trouvée ou token invalide');
        }

        // 2. Afficher la page de confirmation
        return $this->render('annulation.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/confirmer_annulation/{id}/{token}', name: 'confirmer_annulation')]
    public function confirmerAnnulation(EntityManagerInterface $em,int $id, string $token,MailerInterface $mailer,
     LoggerInterface $logger): Response
    {

        $reservation = $em->getRepository(Reservations::class)->find($id);

        if (!$reservation || $reservation->getToken() !== $token) {
            throw $this->createNotFoundException('Réservation non trouvée ou token invalide');
        }

        $panier = $reservation->getPanier();
        $dateArrivee = $panier->getDateArrive();
        $aujourdhui = new \DateTimeImmutable();
        $nbJoursAvant = $aujourdhui->diff($dateArrivee)->days;
        $annulationGratuite = $aujourdhui < $dateArrivee && $nbJoursAvant > 3;

        $montantRetenu = 0;
        if (!$annulationGratuite) {
            $nbNuits = $dateArrivee->diff($panier->getDateDepart())->days;
            $montantRetenu = $reservation->getPrixTotal() / $nbNuits;
        }

        $reservation->setStatus('Annulée');
        $em->persist($reservation);
        $em->flush();

        // Envoi de l'email d'annulation
        try {
            $htmlContent = $this->renderView('emails/reservation_annulation.html.twig', [
                'user' => $reservation->getUser(),
                'reservation' => $reservation,
                'panier' => $panier,
                'montantRetenu' => $montantRetenu,
                'annulationGratuite' => $annulationGratuite,
            ]);
            $dsn = 'smtp://mohamedbenachenhou430@gmail.com:fcunsvkgyjpcsqhz@smtp.gmail.com:587';
            $transport = Transport::fromDsn($dsn);
            
            // Créer manuellement l'objet Mailer
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from(new Address('mohamedbenachenhou430@gmail.com', 'Hôtel Roxal'))
                ->to(new Address($reservation->getUser()->getEmail(), $reservation->getUser()->getPrenom().' '.$reservation->getUser()->getNom()))
                ->subject('Annulation de votre réservation #'.$reservation->getId())
                ->html($htmlContent);

            $mailer->send($email);
            $this->addFlash('success', 'Réservation annulée et email envoyé avec succès.');
        } catch (\Throwable $e) {
            $logger->error('Erreur envoi email d’annulation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reservation_id' => $reservation->getId()
            ]);
            $this->addFlash('warning', 'Réservation annulée, mais erreur lors de l’envoi de l’email.');
        }

        return $this->redirectToRoute('annuler_reservation', [
            'id' => $reservation->getId(),
            'token' => $reservation->getToken(),
        ]);        
    }
}
