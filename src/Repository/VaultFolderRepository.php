<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\VaultFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VaultFolder>
 */
class VaultFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VaultFolder::class);
    }

    public function findByIdAndUser(int $id, User $user): ?VaultFolder
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    /**
     * @return VaultFolder[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.position', Order::Ascending->value)
            ->addOrderBy('f.name', Order::Ascending->value)
            ->getQuery()
            ->getResult();
    }
}
