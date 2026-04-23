<?php

declare(strict_types=1);

namespace App\Tests\Unit\Manager;

use App\DTO\Vault\CreateVaultEntryInput;
use App\Entity\User;
use App\Entity\VaultEntry;
use App\Manager\VaultManager;
use App\Repository\VaultFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class VaultManagerTest extends TestCase
{
    public function testCreatePersistsAndFlushes(): void
    {
        $user = new User();
        $input = $this->makeInput();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(VaultEntry::class));
        $em->expects(self::once())->method('flush');

        $entry = $this->buildManager(em: $em)->create($user, $input);

        self::assertSame($user, $entry->getUser());
        self::assertSame('GitHub', $entry->getTitle());
        self::assertSame('https://github.com', $entry->getUrl());
        self::assertSame('encryptedblob', $entry->getEncryptedData());
        self::assertSame('iv==', $entry->getIv());
        self::assertTrue($entry->isFavorite());
    }

    public function testUpdateAppliesInputAndFlushes(): void
    {
        $entry = new VaultEntry(new User());
        $entry->setTitle('Old')->setEncryptedData('old')->setIv('iv==');

        $input = $this->makeInput(title: 'New', encryptedData: 'newblob');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        $em->expects(self::never())->method('persist');

        $this->buildManager(em: $em)->update($entry, $input);

        self::assertSame('New', $entry->getTitle());
        self::assertSame('newblob', $entry->getEncryptedData());
    }

    public function testDeleteRemovesAndFlushes(): void
    {
        $entry = new VaultEntry(new User());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('remove')->with($entry);
        $em->expects(self::once())->method('flush');

        $this->buildManager(em: $em)->delete($entry);
    }

    private function makeInput(
        string $title = 'GitHub',
        string $encryptedData = 'encryptedblob',
    ): CreateVaultEntryInput {
        return new CreateVaultEntryInput(
            title: $title,
            encryptedData: $encryptedData,
            iv: 'iv==',
            url: 'https://github.com',
            isFavorite: true,
        );
    }

    private function buildManager(?EntityManagerInterface $em = null): VaultManager
    {
        return new VaultManager(
            $em ?? $this->createStub(EntityManagerInterface::class),
            $this->createStub(VaultFolderRepository::class),
        );
    }
}
