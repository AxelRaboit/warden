<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\VaultEntry;
use App\Repository\Trait\PaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VaultEntry>
 */
class VaultEntryRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VaultEntry::class);
    }

    public function findByIdAndUser(int $id, User $user): ?VaultEntry
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    /**
     * @return array{items: VaultEntry[], total: int, page: int, totalPages: int}
     */
    public function findPaginatedByUser(User $user, int $page = 1, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.isFavorite', Order::Descending->value)
            ->addOrderBy('v.title', Order::Ascending->value);

        $countQb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.user = :user')
            ->setParameter('user', $user);

        return $this->paginate($qb, $countQb, $page, $limit);
    }
}
