<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\VaultFolder;

final readonly class VaultFolderSerializer
{
    public function serialize(VaultFolder $folder): array
    {
        return [
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'color' => $folder->getColor(),
            'position' => $folder->getPosition(),
        ];
    }
}
