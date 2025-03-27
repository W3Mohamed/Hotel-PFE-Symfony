<?php

namespace App\Controller;

use App\Entity\Chambres;
use App\Entity\Panier;
use App\Entity\PanierChambres;
use App\Entity\PanierService;
use App\Entity\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        // Récupération des données du formulaire
        $chambreId = $request->request->get('chambre_id');
        $servicesIds = $request->request->all('services',[]);// Tableau des services sélectionnés
        $nbNuits = (int) $request->request->get('nb_nuit', 1); // Nombre de nuits, valeur par défaut = 1

        // Vérifier si la chambre existe
        $chambre = $em->getRepository(Chambres::class)->find($chambreId);
        if (!$chambre) {
            throw $this->createNotFoundException('Chambre non trouvée');
        }

        $sessionId = $session->getId();
        $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId]);

        if (!$panier) {
            $panier = new Panier();
            $panier->setSessionId($sessionId);
            $panier->setDateCreation(new \DateTime());
            $em->persist($panier);
        }

        // Ajouter la chambre au panier
        $panierChambre = new PanierChambres();
        $panierChambre->setPanier($panier);
        $panierChambre->setChambre($chambre);
        $panierChambre->setNbNuit($nbNuits);
        $em->persist($panierChambre);
        
        // Ajouter les services sélectionnés
        foreach ($servicesIds as $serviceId) {
            $service = $em->getRepository(Services::class)->find($serviceId);
            if ($service) {
                $panierService = new PanierService();
                $panierService->setPanierChambre($panierChambre);
                $panierService->setServiceId($service);
                $em->persist($panierService);
            }
        }

        $em->flush();
        
        return $this->render('panier.html.twig', [
            'controller_name' => 'panier',
        ]);
    }
}
