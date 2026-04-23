<?php

declare(strict_types=1);

namespace App\Manager;

use App\Contract\VaultManagerInterface;
use App\DTO\Vault\CreateVaultEntryInput;
use App\Entity\User;
use App\Entity\VaultEntry;
use App\Entity\VaultFolder;
use App\Repository\VaultFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(VaultManagerInterface::class)]
final readonly class VaultManager implements VaultManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VaultFolderRepository $vaultFolderRepository,
    ) {}

    public function create(User $user, CreateVaultEntryInput $input): VaultEntry
    {
        $entry = new VaultEntry($user);
        $this->applyInput($entry, $input, $user);

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $entry;
    }

    public function update(VaultEntry $entry, CreateVaultEntryInput $input): void
    {
        $this->applyInput($entry, $input, $entry->getUser());
        $this->entityManager->flush();
    }

    public function delete(VaultEntry $entry): void
    {
        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }

    private function applyInput(VaultEntry $entry, CreateVaultEntryInput $input, User $user): void
    {
        $entry->setRecordType($input->recordType);
        $entry->setTitle($input->title);
        $entry->setUrl($input->url);
        $entry->setEncryptedData($input->encryptedData);
        $entry->setIv($input->iv);
        $entry->setIsFavorite($input->isFavorite);

        if (null === $input->folderId) {
            $entry->setFolder(null);
        } else {
            $folder = $this->vaultFolderRepository->findByIdAndUser($input->folderId, $user);
            if ($folder instanceof VaultFolder) {
                $entry->setFolder($folder);
            }
        }
    }
}
