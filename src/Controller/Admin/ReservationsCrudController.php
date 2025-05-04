<?php

namespace App\Controller\Admin;

use App\Entity\Reservations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    TextField,
    MoneyField,
    DateTimeField,
    TextareaField,
    AssociationField,
    ChoiceField
};
use EasyCorp\Bundle\EasyAdminBundle\Filter\{
    ChoiceFilter,
    DateTimeFilter
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
            ->setPaginatorPageSize(20)
            ->setDefaultSort(['date_creation' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new')
            ->add('index', 'detail')
            ->update('index', 'edit', function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Modifier statut');
            });
    }

    // public function configureFilters(Filters $filters): Filters
    // {
    //     return $filters
    //         ->add(ChoiceFilter::new('status')->setChoices([
    //             'En cours' => 'En cours',
    //             'Confirmée' => 'Confirmée',
    //             'Annulée' => 'Annulée'
    //         ]))
    //         ->add(DateTimeFilter::new('panier.dateArrive', 'Date d\'arrivée'));
    // }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Client')
                ->formatValue(function ($value, $entity) {
                    $user = $entity->getUser();
                    return $user ? sprintf('%s %s', $user->getPrenom(), $user->getNom()) : '';
                })
                ->onlyOnIndex(),
            
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En cours' => 'En cours',
                    'Confirmée' => 'Confirmée',
                    'Annulée' => 'Annulée'
                ])
                ->renderExpanded()
                ->renderAsBadges([
                    'En cours' => 'warning',
                    'Confirmée' => 'success',
                    'Annulée' => 'danger'
                ]),

            MoneyField::new('prix_total', 'Prix total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
                
            DateTimeField::new('date_creation', 'Date création')
                ->setFormat('dd/MM/Y HH:mm'),
                
            DateTimeField::new('panier.dateArrive', 'Date arrivée')
                ->setFormat('dd/MM/Y')
                ->setCustomOption('widget', 'single_text'),
                
            TextareaField::new('commentaire', 'Commentaire')
                ->hideOnIndex(),
        ];

        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('user.nom', 'Nom')->onlyOnDetail();
            $fields[] = TextField::new('user.prenom', 'Prénom')->onlyOnDetail();
            $fields[] = TextField::new('user.email', 'Email')->onlyOnDetail();
            $fields[] = TextField::new('user.telephone', 'Téléphone')->onlyOnDetail();
            $fields[] = TextField::new('user.adresse', 'Adresse')->onlyOnDetail();
            $fields[] = TextField::new('user.ville', 'Ville')->onlyOnDetail();
            $fields[] = TextField::new('user.code_postale', 'Code postal')->onlyOnDetail();
            $fields[] = TextField::new('user.pays', 'Pays')->onlyOnDetail();
            
            $fields[] = AssociationField::new('panier', 'Détails du panier')
                ->onlyOnDetail()
                ->formatValue(function ($value, $entity) {
                    if (!$entity->getPanier()) {
                        return 'Aucun panier associé';
                    }
                    
                    $panier = $entity->getPanier();
                    $details = [];

                    $details[] = sprintf(
                        "<strong>Dates :</strong> %s → %s<br>
                         <strong>Adultes :</strong> %d<br>
                         <strong>Enfants :</strong> %d",
                        $panier->getDateArrive()->format('d/m/Y'),
                        $panier->getDateDepart()->format('d/m/Y'),
                        $panier->getNbAdulte(),
                        $panier->getNbEnfant()
                    );
                    
                    foreach ($panier->getPanierChambres() as $panierChambre) {
                        $chambre = $panierChambre->getChambre();
                        $services = [];
                        
                        foreach ($panierChambre->getPanierServices() as $panierService) {
                            $services[] = $panierService->getService()->getLibelle()
                                . ' (' . $panierService->getService()->getPrix() . '€)';
                        }
                        
                        $details[] = sprintf(
                            "<strong>%s</strong><br>Services: %s",
                            $chambre->getLibelle(),
                            $services ? implode(', ', $services) : 'Aucun service'
                        );
                    }
                    
                    return implode('<hr>', $details);
                });
        }

        return $fields;
    }
}