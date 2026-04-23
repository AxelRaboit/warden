<?php

declare(strict_types=1);

namespace App\DTO\Vault;

use App\Enum\VaultRecordTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateVaultEntryInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'vault.errors.title_required')]
        #[Assert\Length(max: 255)]
        public string $title,
        #[Assert\NotBlank(message: 'vault.errors.encrypted_data_required')]
        public string $encryptedData,
        #[Assert\NotBlank(message: 'vault.errors.iv_required')]
        public string $iv,
        public VaultRecordTypeEnum $recordType = VaultRecordTypeEnum::Login,
        public ?string $url = null,
        public ?int $folderId = null,
        public bool $isFavorite = false,
    ) {}

    public static function fromArray(array $data): self
    {
        $folderId = $data['folderId'] ?? null;

        return new self(
            title: mb_trim((string) ($data['title'] ?? '')),
            encryptedData: (string) ($data['encryptedData'] ?? ''),
            iv: (string) ($data['iv'] ?? ''),
            recordType: VaultRecordTypeEnum::tryFrom((string) ($data['recordType'] ?? '')) ?? VaultRecordTypeEnum::Login,
            url: mb_trim((string) ($data['url'] ?? '')) ?: null,
            folderId: is_numeric($folderId) ? (int) $folderId : null,
            isFavorite: (bool) ($data['isFavorite'] ?? false),
        );
    }
}
