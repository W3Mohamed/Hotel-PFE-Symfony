<?php

namespace App\Repository;

use App\Entity\Chambres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chambres>
 */
class ChambresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambres::class);
    }

//    /**
//     * @return Chambres[] Returns an array of Chambres objects
//     */
    public function findAvailableRooms(?array $unavailableRoomIds = null)
    {
        $qb = $this->createQueryBuilder('c');
        
        if ($unavailableRoomIds) {
            $qb->where('c.id NOT IN (:unavailableRoomIds)')
               ->setParameter('unavailableRoomIds', $unavailableRoomIds);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    public function findUnavailableRoomsForDates(\DateTimeInterface $dateArrive, \DateTimeInterface $dateDepart): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('DISTINCT c.id') // On veut seulement les IDs uniques
            ->join('c.panierChambres', 'pc') // Jointure avec PanierChambres
            ->join('pc.panier', 'p') // Jointure avec Panier
            ->join('App\Entity\Reservations', 'r', 'WITH', 'r.panier = p.id') // Jointure avec Réservation
            ->where('r.status = :status') // Uniquement les réservations confirmées
            ->andWhere('p.dateArrive < :dateDepart AND p.dateDepart > :dateArrive') // Chevauchement de dates
            ->setParameter('status', 'Confirmée')
            ->setParameter('dateArrive', $dateArrive)
            ->setParameter('dateDepart', $dateDepart)
            ->getQuery()
            ->getResult();

        // Retourne un tableau simple d'IDs [1, 5, 8] au lieu de [['id'=>1], ['id'=>5], ...]
        return array_column($results, 'id');
    }
}
