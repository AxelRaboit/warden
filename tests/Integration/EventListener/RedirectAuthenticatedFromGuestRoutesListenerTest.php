<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventListener;

use App\Entity\User;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class RedirectAuthenticatedFromGuestRoutesListenerTest extends IntegrationTestCase
{
    public function testUnauthenticatedUserCanAccessLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
    }

    public function testAuthenticatedUserIsRedirectedFromLoginPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('GET', '/login');

        self::assertResponseRedirects('/admin');
    }

    public function testAuthenticatedUserIsRedirectedFromRegisterPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('GET', '/register');

        self::assertResponseRedirects('/admin');
    }

    public function testAuthenticatedUserIsRedirectedFromForgotPasswordPage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('GET', '/forgot-password');

        self::assertResponseRedirects('/admin');
    }

    public function testAuthenticatedUserCanStillAccessDashboard(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getAdminUser());

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }

    private function getAdminUser(): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em->getRepository(User::class)->findOneBy(['email' => 'admin@warden.app']);
    }
}
