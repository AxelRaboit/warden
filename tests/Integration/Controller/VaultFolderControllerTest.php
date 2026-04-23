<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\Entity\VaultFolder;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class VaultFolderControllerTest extends IntegrationTestCase
{
    public function testListFoldersRedirectsWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vault/folders');

        self::assertResponseRedirects();
    }

    public function testListFoldersReturnsJsonItems(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('GET', '/vault/folders');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('items', $data);
    }

    public function testCreateFolderReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('POST', '/vault/folders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Work',
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
        self::assertSame('Work', $data['folder']['name']);
    }

    public function testCreateFolderFailsWhenNameEmpty(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('POST', '/vault/folders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => '',
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertFalse($data['success']);
        self::assertArrayHasKey('name', $data['errors']);
    }

    public function testUpdateFolderReturnsSuccess(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $folder = $this->createFolder($em, $user, 'Old');

        $client->request('PATCH', '/vault/folders/'.$folder->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'New',
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
        self::assertSame('New', $data['folder']['name']);
    }

    public function testUpdateFolderReturns404ForNonOwned(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('PATCH', '/vault/folders/99999', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Hack',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteFolderReturnsSuccess(): void
    {
        $client = static::createClient();
        $user = $this->getAdminUser();
        $client->loginUser($user);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $folder = $this->createFolder($em, $user, 'To delete');

        $client->request('DELETE', '/vault/folders/'.$folder->getId());

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
    }

    public function testDeleteFolderReturns404ForNonOwned(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('DELETE', '/vault/folders/99999');

        self::assertResponseStatusCodeSame(404);
    }

    private function getAdminUser(): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em->getRepository(User::class)->findOneBy(['email' => 'admin@warden.app']);
    }

    private function createFolder(EntityManagerInterface $em, User $user, string $name): VaultFolder
    {
        $folder = new VaultFolder($user, $name);
        $em->persist($folder);
        $em->flush();

        return $folder;
    }
}
