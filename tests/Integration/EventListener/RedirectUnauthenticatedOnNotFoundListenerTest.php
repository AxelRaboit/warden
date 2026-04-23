<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventListener;

use App\Entity\User;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class RedirectUnauthenticatedOnNotFoundListenerTest extends IntegrationTestCase
{
    public function testUnauthenticatedUserIsRedirectedToLoginOnUnknownRoute(): void
    {
        $client = static::createClient();
        $client->request('GET', '/this/path/does/not/exist');

        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserStillGets404OnUnknownRoute(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'admin@warden.app']);
        $client->loginUser($user);

        $client->request('GET', '/this/path/does/not/exist');

        self::assertResponseStatusCodeSame(404);
    }
}
