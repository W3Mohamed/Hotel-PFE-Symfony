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
    #[Route('/panier', name: 'ajout_panier', methods: ['POST'])]
    public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        // Récupération des données du formulaire
        $chambreId = $request->request->get('chambre_id');
        $dateArrive = $request->request->get('dateArrive');
        $dateDepart = $request->request->get('dateDepart');
        $nbAdulte = $request->request->get('nbAdulte');
        $nbEnfant = $request->request->get('nbEnfant');
        $servicesIds = $request->request->all('services',[]);// Tableau des services sélectionnés
        //$nbNuits = (int) $request->request->get('nb_nuit', 1); // Nombre de nuits, valeur par défaut = 1

        // Vérifier si la chambre existe
        $chambre = $em->getRepository(Chambres::class)->find($chambreId);
        if (!$chambre) {
            throw $this->createNotFoundException('Chambre non trouvée');
        }
        // // Assurer que la session est démarrée
        // if (!$session->isStarted()) {
        //     $session->start();
        // }
        $sessionId = $session->getId();
        if (!$sessionId) {
            throw new \Exception("L'ID de session est introuvable.");
        }
        $panier = $em->getRepository(Panier::class)->findOneBy(['session_id' => $sessionId]);

        if (!$panier) {
            $panier = new Panier();
            $panier->setSessionId($sessionId);
            $panier->setDateArrive(new \DateTime($dateArrive));
            $panier->setDateDepart(new \DateTime($dateDepart));
            $panier->setNbAdulte($nbAdulte);
            $panier->setNbEnfant($nbEnfant);
            $panier->setDateCreation(new \DateTime());
            $panier->setStatus(false);
            $em->persist($panier);
            $em->flush();
        }

        $panierChambreExistant = $em->getRepository(PanierChambres::class)->findOneBy([
            'panier' => $panier,
            'chambre' => $chambre
        ]);
        if ($panierChambreExistant) {
            $this->addFlash('chambre_existe', 'Cette chambre est déjà dans votre panier.');
            return $this->redirectToRoute('detail', ['id' => $chambreId]); // Redirection vers la page détail
        }

        // Ajouter la chambre au panier
        $panierChambre = new PanierChambres();
        $panierChambre->setPanier($panier);
        $panierChambre->setChambre($chambre);
        //$panierChambre->setNbNuit($nbNuits);
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
        
        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/delete/{id}', name: 'sup_chambre', methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $em, SessionInterface $session):Response
    {
        $sessionId = $session->getId();
        $panierChambre = $em->getRepository(PanierChambres::class)->find($id);

        if (!$panierChambre) {
            $this->addFlash('error', 'Chambre non trouvée dans votre panier.');
            return $this->redirectToRoute('panier');
        }

        if ($panierChambre->getPanier()->getSessionId() !== $sessionId) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('panier');
        }

         // Supprimer les services associés
        foreach ($panierChambre->getPanierServices() as $service) {
            $em->remove($service);
        }
        // Supprimer la chambre du panier
        $em->remove($panierChambre);
        $em->flush();

        return $this->redirectToRoute('panier');
    }
}
