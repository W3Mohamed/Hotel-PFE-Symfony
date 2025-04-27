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
use Symfony\Component\HttpFoundation\RequestStack;

class ReservationsCrudController extends AbstractCrudController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

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
            ->setDefaultSort(['panier.dateArrive' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $upcomingAction = Action::new('upcoming', 'À venir (3 jours)')
            ->linkToCrudAction('index')
            ->setCssClass('btn btn-warning');
            // ->setQueryParameter('upcoming', true);

        return $actions
            ->disable('new')
            ->add('index', 'detail')
            ->update('index', 'edit', function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Modifier statut');
            })
            ->add('index', $upcomingAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices([
                'En cours' => 'En cours',
                'Confirmée' => 'Confirmée',
                'Annulée' => 'Annulée'
            ]))
            ->add(DateTimeFilter::new('panier.dateArrive', 'Date d\'arrivée'));
    }

    // public function createIndexQueryBuilder($entityClass, $sortField, $sortDirection)
    // {
    //     $qb = parent::createIndexQueryBuilder($entityClass, $sortField, $sortDirection);
        
    //     $request = $this->requestStack->getCurrentRequest();
    //     if ($request && $request->query->get('upcoming')) {
    //         $now = new \DateTime();
    //         $in3Days = (new \DateTime())->modify('+3 days');
            
    //         $qb->join('entity.panier', 'p')
    //            ->andWhere('entity.status = :status')
    //            ->andWhere('p.dateArrive BETWEEN :now AND :in3Days')
    //            ->setParameter('status', 'Confirmée')
    //            ->setParameter('now', $now)
    //            ->setParameter('in3Days', $in3Days);
    //     }
        
    //     return $qb;
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
                ->setCustomOption('widget', 'single_text')
                ->formatValue(function ($value, $entity) {
                    if (!$entity->getPanier()) {
                        return '';
                    }
                    
                    $dateArrive = $entity->getPanier()->getDateArrive();
                    $now = new \DateTime();
                    $diff = $now->diff($dateArrive);
                    
                    if ($entity->getStatus() === 'Confirmée' && $diff->days <= 3 && !$diff->invert) {
                        return sprintf(
                            '<span class="badge badge-warning">%s (dans %d jours)</span>',
                            $dateArrive->format('d/m/Y'),
                            $diff->days
                        );
                    }
                    
                    return $dateArrive->format('d/m/Y');
                }),
                
            TextareaField::new('commentaire', 'Commentaire')
                ->hideOnIndex(),
        ];

        // Champs détaillés
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
                            "<strong>%s × %d</strong><br>Services: %s",
                            $chambre->getLibelle(),
                            $panierChambre->getQuantite(),
                            $services ? implode(', ', $services) : 'Aucun service'
                        );
                    }
                    
                    return implode('<hr>', $details);
                });
        }

        return $fields;
    }
}