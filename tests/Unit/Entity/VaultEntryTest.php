<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\VaultEntry;
use App\Entity\VaultFolder;
use App\Enum\VaultRecordTypeEnum;
use PHPUnit\Framework\TestCase;

final class VaultEntryTest extends TestCase
{
    public function testConstructorSetsUser(): void
    {
        $user = new User();
        $entry = new VaultEntry($user);

        self::assertSame($user, $entry->getUser());
    }

    public function testIdIsNullBeforePersist(): void
    {
        $entry = new VaultEntry(new User());

        self::assertNull($entry->getId());
    }

    public function testDefaultIsFavoriteIsFalse(): void
    {
        $entry = new VaultEntry(new User());

        self::assertFalse($entry->isFavorite());
    }

    public function testDefaultUrlIsNull(): void
    {
        $entry = new VaultEntry(new User());

        self::assertNull($entry->getUrl());
    }

    public function testSettersReturnStatic(): void
    {
        $entry = new VaultEntry(new User());

        self::assertSame($entry, $entry->setTitle('GitHub'));
        self::assertSame($entry, $entry->setUrl('https://github.com'));
        self::assertSame($entry, $entry->setEncryptedData('encrypted'));
        self::assertSame($entry, $entry->setIv('iv=='));
        self::assertSame($entry, $entry->setIsFavorite(true));
    }

    public function testGettersReturnSetValues(): void
    {
        $entry = new VaultEntry(new User());
        $entry->setTitle('GitHub')
              ->setUrl('https://github.com')
              ->setEncryptedData('encryptedblob')
              ->setIv('randomiv==')
              ->setIsFavorite(true);

        self::assertSame('GitHub', $entry->getTitle());
        self::assertSame('https://github.com', $entry->getUrl());
        self::assertSame('encryptedblob', $entry->getEncryptedData());
        self::assertSame('randomiv==', $entry->getIv());
        self::assertTrue($entry->isFavorite());
    }

    public function testSetUrlAcceptsNull(): void
    {
        $entry = new VaultEntry(new User());
        $entry->setUrl('https://example.com');
        $entry->setUrl(null);

        self::assertNull($entry->getUrl());
    }

    public function testDefaultRecordTypeIsLogin(): void
    {
        $entry = new VaultEntry(new User());

        self::assertSame(VaultRecordTypeEnum::Login, $entry->getRecordType());
    }

    public function testSetRecordType(): void
    {
        $entry = new VaultEntry(new User());
        $entry->setRecordType(VaultRecordTypeEnum::SshKey);

        self::assertSame(VaultRecordTypeEnum::SshKey, $entry->getRecordType());
    }

    public function testDefaultFolderIsNull(): void
    {
        $entry = new VaultEntry(new User());

        self::assertNull($entry->getFolder());
    }

    public function testSetFolderAndClear(): void
    {
        $user = new User();
        $entry = new VaultEntry($user);
        $folder = new VaultFolder($user, 'Work');

        $entry->setFolder($folder);
        self::assertSame($folder, $entry->getFolder());

        $entry->setFolder(null);
        self::assertNull($entry->getFolder());
    }
}
