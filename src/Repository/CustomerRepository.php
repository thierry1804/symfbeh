<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function save(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Recherche avancée de clients avec filtres
     */
    public function findByFilters(array $filters = [], array $orderBy = ['createdAt' => 'DESC'], int $limit = null, int $offset = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u');

        // Filtres
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.firstName', ':search'),
                    $qb->expr()->like('c.lastName', ':search'),
                    $qb->expr()->like('c.email', ':search'),
                    $qb->expr()->like('c.company', ':search'),
                    $qb->expr()->like('c.phone', ':search'),
                    $qb->expr()->like('c.mobile', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('c.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $qb->andWhere('c.source = :source')
               ->setParameter('source', $filters['source']);
        }

        if (!empty($filters['assignedTo'])) {
            $qb->andWhere('c.assignedTo = :assignedTo')
               ->setParameter('assignedTo', $filters['assignedTo']);
        }

        if (isset($filters['isActive'])) {
            $qb->andWhere('c.isActive = :isActive')
               ->setParameter('isActive', $filters['isActive']);
        }

        if (!empty($filters['city'])) {
            $qb->andWhere('c.city = :city')
               ->setParameter('city', $filters['city']);
        }

        if (!empty($filters['country'])) {
            $qb->andWhere('c.country = :country')
               ->setParameter('country', $filters['country']);
        }

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('c.createdAt >= :dateFrom')
               ->setParameter('dateFrom', $filters['dateFrom']);
        }

        if (!empty($filters['dateTo'])) {
            $qb->andWhere('c.createdAt <= :dateTo')
               ->setParameter('dateTo', $filters['dateTo']);
        }

        // Tri
        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy('c.' . $field, $direction);
        }

        // Pagination
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les clients qui nécessitent un suivi
     */
    public function findCustomersNeedingFollowUp(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u')
            ->where('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.nextFollowUpAt', 'ASC');

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('c.nextFollowUpAt'),
                $qb->expr()->lte('c.nextFollowUpAt', ':now')
            )
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les clients par statut
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u')
            ->andWhere('c.status = :status')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('status', $status)
            ->setParameter('isActive', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les clients assignés à un utilisateur
     */
    public function findByAssignedUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.assignedTo = :user')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('user', $user)
            ->setParameter('isActive', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les clients non assignés
     */
    public function findUnassignedCustomers(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.assignedTo IS NULL')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des clients par statut
     */
    public function getStatusStatistics(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.status, COUNT(c.id) as count')
            ->where('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->groupBy('c.status');

        $results = $qb->getQuery()->getResult();

        $statistics = [
            'prospect' => 0,
            'client' => 0,
            'inactif' => 0,
            'vip' => 0,
        ];

        foreach ($results as $result) {
            $statistics[$result['status']] = $result['count'];
        }

        return $statistics;
    }

    /**
     * Statistiques des clients par source
     */
    public function getSourceStatistics(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.source, COUNT(c.id) as count')
            ->where('c.isActive = :isActive')
            ->andWhere('c.source IS NOT NULL')
            ->setParameter('isActive', true)
            ->groupBy('c.source')
            ->orderBy('count', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les clients créés récemment
     */
    public function findRecentCustomers(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u')
            ->andWhere('c.createdAt >= :date')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('date', $date)
            ->setParameter('isActive', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les clients VIP
     */
    public function findVipCustomers(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u')
            ->andWhere('c.status = :status')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('status', 'vip')
            ->setParameter('isActive', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par tags
     */
    public function findByTags(array $tags): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.assignedTo', 'u')
            ->addSelect('u');

        $conditions = [];
        foreach ($tags as $index => $tag) {
            $conditions[] = $qb->expr()->like('c.tags', ':tag' . $index);
            $qb->setParameter('tag' . $index, '%' . $tag . '%');
        }

        if (!empty($conditions)) {
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        $qb->andWhere('c.isActive = :isActive')
           ->setParameter('isActive', true)
           ->orderBy('c.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve tous les tags utilisés
     */
    public function findAllTags(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.tags')
            ->where('c.tags IS NOT NULL')
            ->andWhere('c.tags != :empty')
            ->setParameter('empty', '[]');

        $results = $qb->getQuery()->getResult();
        $allTags = [];

        foreach ($results as $result) {
            $tags = json_decode($result['tags'], true);
            if (is_array($tags)) {
                $allTags = array_merge($allTags, $tags);
            }
        }

        return array_unique($allTags);
    }
}
