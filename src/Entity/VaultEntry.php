<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\VaultRecordTypeEnum;
use App\Repository\VaultEntryRepository;
use App\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VaultEntryRepository::class)]
#[ORM\Table(name: 'vault_entries')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'IDX_vault_entries_user_id', columns: ['user_id'])]
class VaultEntry
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VaultFolder::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?VaultFolder $folder = null;

    #[ORM\Column(length: 32, enumType: VaultRecordTypeEnum::class)]
    private VaultRecordTypeEnum $recordType = VaultRecordTypeEnum::Login;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $encryptedData;

    #[ORM\Column(length: 24)]
    private string $iv;

    #[ORM\Column]
    private bool $isFavorite = false;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFolder(): ?VaultFolder
    {
        return $this->folder;
    }

    public function setFolder(?VaultFolder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getRecordType(): VaultRecordTypeEnum
    {
        return $this->recordType;
    }

    public function setRecordType(VaultRecordTypeEnum $recordType): static
    {
        $this->recordType = $recordType;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getEncryptedData(): string
    {
        return $this->encryptedData;
    }

    public function setEncryptedData(string $encryptedData): static
    {
        $this->encryptedData = $encryptedData;

        return $this;
    }

    public function getIv(): string
    {
        return $this->iv;
    }

    public function setIv(string $iv): static
    {
        $this->iv = $iv;

        return $this;
    }

    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }
}
