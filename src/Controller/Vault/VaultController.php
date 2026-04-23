<?php

declare(strict_types=1);

namespace App\Controller\Vault;

use App\Contract\VaultManagerInterface;
use App\Controller\Trait\ApiResponseTrait;
use App\Controller\Trait\VaultOwnershipTrait;
use App\DTO\Vault\CreateVaultEntryInput;
use App\Entity\User;
use App\Entity\VaultEntry;
use App\Enum\HttpMethodEnum;
use App\Enum\UserRoleEnum;
use App\Repository\VaultEntryRepository;
use App\Serializer\VaultEntrySerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/vault', name: 'vault')]
#[IsGranted(UserRoleEnum::User->value)]
final class VaultController extends AbstractController
{
    use ApiResponseTrait;
    use VaultOwnershipTrait;

    public function __construct(
        private readonly VaultManagerInterface $vaultManager,
        private readonly VaultEntryRepository $vaultEntryRepository,
        private readonly VaultEntrySerializer $serializer,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: '')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('vault/index.html.twig', [
            'argon2Salt' => $user->getArgon2Salt(),
        ]);
    }

    #[Route('/entries', name: '_entries_list', methods: [HttpMethodEnum::Get->value])]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $page = max(1, (int) $request->query->get('page', 1));

        $result = $this->vaultEntryRepository->findPaginatedByUser($user, $page);

        return $this->json([
            'items' => array_map($this->serializer->serialize(...), $result['items']),
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    #[Route('/entries', name: '_entries_create', methods: [HttpMethodEnum::Post->value])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $input = CreateVaultEntryInput::fromArray(json_decode($request->getContent(), true) ?? []);

        if (($response = $this->validateInput($input)) instanceof JsonResponse) {
            return $response;
        }

        $entry = $this->vaultManager->create($user, $input);

        return $this->apiSuccess(['id' => $entry->getId()]);
    }

    #[Route('/entries/{id}', name: '_entries_update', methods: [HttpMethodEnum::Patch->value])]
    public function update(int $id, Request $request): JsonResponse
    {
        return $this->withOwned($id, $this->vaultEntryRepository->findByIdAndUser(...), function (VaultEntry $entry) use ($request): JsonResponse {
            $input = CreateVaultEntryInput::fromArray(json_decode($request->getContent(), true) ?? []);

            if (($response = $this->validateInput($input)) instanceof JsonResponse) {
                return $response;
            }

            $this->vaultManager->update($entry, $input);

            return $this->apiSuccess();
        });
    }

    #[Route('/entries/{id}', name: '_entries_delete', methods: [HttpMethodEnum::Delete->value])]
    public function delete(int $id): JsonResponse
    {
        return $this->withOwned($id, $this->vaultEntryRepository->findByIdAndUser(...), function (VaultEntry $entry): JsonResponse {
            $this->vaultManager->delete($entry);

            return $this->apiSuccess();
        });
    }
}
