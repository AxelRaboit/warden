<?php

declare(strict_types=1);

namespace App\Contract;

use App\DTO\Vault\VaultFolderInput;
use App\Entity\User;
use App\Entity\VaultFolder;

interface VaultFolderManagerInterface
{
    public function create(User $user, VaultFolderInput $input): VaultFolder;

    public function update(VaultFolder $folder, VaultFolderInput $input): void;

    public function delete(VaultFolder $folder): void;
}
