<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $fixtures = $container->get(AppFixtures::class);

        $executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
        $executor->execute([$fixtures]);

        static::ensureKernelShutdown();
    }

    /**
     * Decode the props passed to a Vue component mounted via `{{ vue_component('Name', {...}) }}`.
     *
     * @return array<string, mixed>
     */
    protected static function getVueProps(KernelBrowser $client, string $componentName): array
    {
        $selector = sprintf('[data-symfony--ux-vue--vue-component-value="%s"]', $componentName);
        $node = $client->getCrawler()->filter($selector);

        if (0 === $node->count()) {
            return [];
        }

        $raw = $node->attr('data-symfony--ux-vue--vue-props-value');

        if (null === $raw || '' === $raw) {
            return [];
        }

        return json_decode($raw, true) ?? [];
    }
}
