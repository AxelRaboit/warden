<?php

declare(strict_types=1);

namespace App\DTO\Vault;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VaultFolderInput
{
    public function __construct(
        #[Assert\NotBlank(message: 'vault.errors.folder_name_required')]
        #[Assert\Length(max: 100)]
        public string $name,
        public ?string $color = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $color = mb_trim((string) ($data['color'] ?? ''));

        return new self(
            name: mb_trim((string) ($data['name'] ?? '')),
            color: '' === $color ? null : $color,
        );
    }
}
