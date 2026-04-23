<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\VaultEntry;
use DateTimeInterface;

final readonly class VaultEntrySerializer
{
    public function serialize(VaultEntry $entry): array
    {
        return [
            'id' => $entry->getId(),
            'recordType' => $entry->getRecordType()->value,
            'title' => $entry->getTitle(),
            'url' => $entry->getUrl(),
            'folderId' => $entry->getFolder()?->getId(),
            'encryptedData' => $entry->getEncryptedData(),
            'iv' => $entry->getIv(),
            'isFavorite' => $entry->isFavorite(),
            'createdAt' => $entry->getCreatedAt()->format(DateTimeInterface::ATOM),
            'updatedAt' => $entry->getUpdatedAt()->format(DateTimeInterface::ATOM),
        ];
    }
}
