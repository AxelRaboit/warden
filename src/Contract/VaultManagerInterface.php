<?php

declare(strict_types=1);

namespace App\Contract;

use App\DTO\Vault\CreateVaultEntryInput;
use App\Entity\User;
use App\Entity\VaultEntry;

interface VaultManagerInterface
{
    public function create(User $user, CreateVaultEntryInput $input): VaultEntry;

    public function update(VaultEntry $entry, CreateVaultEntryInput $input): void;

    public function delete(VaultEntry $entry): void;
}
