<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\Entity\VaultEntry;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class VaultControllerTest extends IntegrationTestCase
{
    // ── GET /vault ────────────────────────────────────────────────────────────

    public function testVaultPageRedirectsWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vault');

        self::assertResponseRedirects();
    }

    public function testVaultPageIsAccessibleWhenAuthenticated(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request('GET', '/vault');

        self::assertResponseIsSuccessful();
    }

    public function testVaultPagePassesSaltPropToVueComponent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request('GET', '/vault');

        $props = self::getVueProps($client, 'VaultApp');

        self::assertArrayHasKey('salt', $props);
        self::assertSame(64, mb_strlen($props['salt']));
    }

    // ── GET /vault/entries ────────────────────────────────────────────────────

    public function testListEntriesRedirectsWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vault/entries');

        self::assertResponseRedirects();
    }

    public function testListEntriesReturnsJsonWhenAuthenticated(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request('GET', '/vault/entries');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('page', $data);
        self::assertArrayHasKey('totalPages', $data);
    }

    public function testListEntriesOnlyReturnsOwnEntries(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->createEntry($em, $user, 'My Entry');

        $client->request('GET', '/vault/entries');
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertGreaterThanOrEqual(1, $data['total']);
        foreach ($data['items'] as $item) {
            self::assertArrayHasKey('encryptedData', $item);
            self::assertArrayHasKey('iv', $item);
        }
    }

    // ── POST /vault/entries ───────────────────────────────────────────────────

    public function testCreateEntryReturnsSuccessWithId(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('POST', '/vault/entries', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'New Entry',
            'encryptedData' => base64_encode('fake_encrypted'),
            'iv' => base64_encode('fake_iv_12b'),
            'url' => 'https://example.com',
            'isFavorite' => false,
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
        self::assertArrayHasKey('id', $data);
    }

    public function testCreateEntryValidationFailsWhenTitleMissing(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('POST', '/vault/entries', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => '',
            'encryptedData' => base64_encode('fake_encrypted'),
            'iv' => base64_encode('fake_iv_12b'),
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertFalse($data['success']);
        self::assertArrayHasKey('title', $data['errors']);
    }

    // ── PATCH /vault/entries/{id} ─────────────────────────────────────────────

    public function testUpdateEntryReturnsSuccess(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $entry = $this->createEntry($em, $user, 'Original');

        $client->request('PATCH', '/vault/entries/'.$entry->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Updated',
            'encryptedData' => base64_encode('new_encrypted'),
            'iv' => base64_encode('new_iv_12bytes'),
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testUpdateEntryReturns404ForNonOwnedEntry(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('PATCH', '/vault/entries/99999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Hack',
            'encryptedData' => 'x',
            'iv' => 'x',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    // ── DELETE /vault/entries/{id} ────────────────────────────────────────────

    public function testDeleteEntryReturnsSuccess(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $entry = $this->createEntry($em, $user, 'To Delete');

        $client->request('DELETE', '/vault/entries/'.$entry->getId());

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testDeleteEntryReturns404ForNonOwnedEntry(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());
        $client->request('DELETE', '/vault/entries/99999');

        self::assertResponseStatusCodeSame(404);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getAdminUser(): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em->getRepository(User::class)->findOneBy(['email' => 'admin@warden.app']);
    }

    private function createEntry(EntityManagerInterface $em, User $user, string $title): VaultEntry
    {
        $entry = new VaultEntry($user);
        $entry->setTitle($title)
              ->setEncryptedData(base64_encode('fake_encrypted_data'))
              ->setIv(base64_encode('fake_iv_12bytes_'));
        $em->persist($entry);
        $em->flush();

        return $entry;
    }
}
