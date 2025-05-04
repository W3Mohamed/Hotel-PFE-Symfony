<?php
// src/Repository/FaqRepository.php
namespace App\Repository;

use App\Entity\Faq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function findByCategoryOrdered($categoryId)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.category = :category')
            ->setParameter('category', $categoryId)
            ->orderBy('f.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}