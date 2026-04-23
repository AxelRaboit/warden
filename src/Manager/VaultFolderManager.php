<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\VaultFolderManagerInterface;
use App\DTO\Vault\VaultFolderInput;
use App\Entity\User;
use App\Entity\VaultFolder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(VaultFolderManagerInterface::class)]
final readonly class VaultFolderManager implements VaultFolderManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function create(User $user, VaultFolderInput $input): VaultFolder
    {
        $folder = new VaultFolder($user, $input->name);
        $folder->setColor($input->color);

        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        return $folder;
    }

    public function update(VaultFolder $folder, VaultFolderInput $input): void
    {
        $folder->setName($input->name);
        $folder->setColor($input->color);

        $this->entityManager->flush();
    }

    public function delete(VaultFolder $folder): void
    {
        $this->entityManager->remove($folder);
        $this->entityManager->flush();
    }
}
