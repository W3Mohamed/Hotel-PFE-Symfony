<?php

// src/Controller/Admin/ReservationsCrudController.php

namespace App\Controller\Admin;

use App\Entity\Reservations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    TextField,
    MoneyField,
    DateTimeField,
    TextareaField,
    AssociationField
};

class ReservationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservations::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réservation')
            ->setEntityLabelInPlural('Réservations')
            ->setPageTitle('index', 'Gestion des réservations')
            ->setPaginatorPageSize(20);
            // Désactive la création et l'édition
           // ->disableNewAndEditButtons();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Désactive toutes les actions sauf "voir"
            ->disable('new', 'edit', 'delete')
            // Personnalise l'action "voir"
            ->add('index', 'detail');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Client')
                ->formatValue(function ($value, $entity) {
                    $user = $entity->getUser();
                    return sprintf('%s %s', $user->getPrenom(), $user->getNom());
                })
                ->onlyOnIndex(),
            
            TextField::new('status', 'Statut'),
            MoneyField::new('prix_total', 'Prix total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            DateTimeField::new('date_creation', 'Date création')
                ->setFormat('dd/MM/Y HH:mm'),
            TextareaField::new('commentaire', 'Commentaire')
                ->hideOnIndex(),
            
            // Section détaillée pour le client
            TextField::new('user.nom', 'Nom')
                ->onlyOnDetail(),
            TextField::new('user.prenom', 'Prénom')
                ->onlyOnDetail(),
            TextField::new('user.email', 'Email')
                ->onlyOnDetail(),
            TextField::new('user.telephone', 'Téléphone')
                ->onlyOnDetail(),
            TextField::new('user.adresse', 'Adresse')
                ->onlyOnDetail(),
            TextField::new('user.ville', 'Ville')
                ->onlyOnDetail(),
            TextField::new('user.code_postale', 'Code postal')
                ->onlyOnDetail(),
            TextField::new('user.pays', 'Pays')
                ->onlyOnDetail(),
            
            // Affichage détaillé du panier
            AssociationField::new('panier', 'Détails du panier')
                ->onlyOnDetail()
                ->formatValue(function ($value, $entity) {
                    $panier = $entity->getPanier();
                    $details = [];

                    // Infos générales du panier
                    $details[] = sprintf(
                        "<strong>Dates :</strong> %s → %s<br><strong>Adultes :</strong> %d<br><strong>Enfants :</strong> %d",
                        $panier->getDateArrive()->format('d/m/Y'),
                        $panier->getDateDepart()->format('d/m/Y'),
                        $panier->getNbAdulte(),
                        $panier->getNbEnfant()
                    );
                    
                    foreach ($panier->getPanierChambres() as $panierChambre) {
                        $chambre = $panierChambre->getChambre();
                        $services = [];
                        
                        foreach ($panierChambre->getPanierServices() as $panierService) {
                            $services[] = $panierService->getServiceId()->getLibelle()
                                . ' (' . $panierService->getServiceId()->getPrix() . '€)';
                        }
                        
                        $details[] = sprintf(
                            "<strong>%s</strong><br>Services: %s",
                            $chambre->getLibelle(),
                            $services ? implode(', ', $services) : 'Aucun service'
                        );
                    }
                    
                    return implode('<hr>', $details);
                }),
        ];
    }
}