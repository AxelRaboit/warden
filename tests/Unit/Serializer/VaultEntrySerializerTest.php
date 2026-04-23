<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use App\Entity\User;
use App\Entity\VaultEntry;
use App\Entity\VaultFolder;
use App\Enum\VaultRecordTypeEnum;
use App\Serializer\VaultEntrySerializer;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class VaultEntrySerializerTest extends TestCase
{
    public function testSerializeReturnsExpectedKeys(): void
    {
        $entry = $this->makeEntry();

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('recordType', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('url', $result);
        self::assertArrayHasKey('folderId', $result);
        self::assertArrayHasKey('encryptedData', $result);
        self::assertArrayHasKey('iv', $result);
        self::assertArrayHasKey('isFavorite', $result);
        self::assertArrayHasKey('createdAt', $result);
        self::assertArrayHasKey('updatedAt', $result);
    }

    public function testSerializeIncludesRecordType(): void
    {
        $entry = $this->makeEntry();
        $entry->setRecordType(VaultRecordTypeEnum::PaymentCard);

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertSame('payment_card', $result['recordType']);
    }

    public function testSerializeFolderIdWhenFolderAssigned(): void
    {
        $entry = $this->makeEntry();
        $folder = new VaultFolder(new User(), 'Work');
        $folderIdProp = new ReflectionProperty(VaultFolder::class, 'id');
        $folderIdProp->setValue($folder, 42);
        $entry->setFolder($folder);

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertSame(42, $result['folderId']);
    }

    public function testSerializeFolderIdIsNullWhenNoFolder(): void
    {
        $entry = $this->makeEntry();

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertNull($result['folderId']);
    }

    public function testSerializeReturnsCorrectValues(): void
    {
        $entry = $this->makeEntry();

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertSame(1, $result['id']);
        self::assertSame('GitHub', $result['title']);
        self::assertSame('https://github.com', $result['url']);
        self::assertSame('encryptedblob', $result['encryptedData']);
        self::assertSame('iv==', $result['iv']);
        self::assertTrue($result['isFavorite']);
    }

    public function testSerializeDatesAreAtomFormat(): void
    {
        $entry = $this->makeEntry();

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['createdAt']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result['updatedAt']);
    }

    public function testSerializeNullUrl(): void
    {
        $entry = $this->makeEntry(url: null);

        $result = (new VaultEntrySerializer())->serialize($entry);

        self::assertNull($result['url']);
    }

    private function makeEntry(?string $url = 'https://github.com'): VaultEntry
    {
        $entry = new VaultEntry(new User());
        $entry->setTitle('GitHub')
              ->setUrl($url)
              ->setEncryptedData('encryptedblob')
              ->setIv('iv==')
              ->setIsFavorite(true);

        $idProp = new ReflectionProperty(VaultEntry::class, 'id');
        $idProp->setValue($entry, 1);

        $createdAtProp = new ReflectionProperty(VaultEntry::class, 'createdAt');
        $createdAtProp->setValue($entry, new DateTimeImmutable('2025-01-01T10:00:00+00:00'));

        $updatedAtProp = new ReflectionProperty(VaultEntry::class, 'updatedAt');
        $updatedAtProp->setValue($entry, new DateTimeImmutable('2025-01-02T10:00:00+00:00'));

        return $entry;
    }
}
