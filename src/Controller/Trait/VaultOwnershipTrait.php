<?php

declare(strict_types=1);

namespace App\Controller\Trait;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

trait VaultOwnershipTrait
{
    use ApiResponseTrait;

    /**
     * Fetch an entity owned by the current user and execute the action with it.
     * Returns a 404 JsonResponse if the entity is not found or not owned.
     *
     * @param callable(int, User): ?object   $finder Repository method accepting (id, user)
     * @param callable(object): JsonResponse $action Action to run on the owned entity
     */
    private function withOwned(int $id, callable $finder, callable $action): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $entity = $finder($id, $user);

        if (null === $entity) {
            return $this->apiNotFound();
        }

        return $action($entity);
    }
}
