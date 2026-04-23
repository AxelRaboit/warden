<?php

declare(strict_types=1);

namespace App\Controller\Vault;

use App\Contract\VaultFolderManagerInterface;
use App\Controller\Trait\ApiResponseTrait;
use App\Controller\Trait\VaultOwnershipTrait;
use App\DTO\Vault\VaultFolderInput;
use App\Entity\User;
use App\Entity\VaultFolder;
use App\Enum\HttpMethodEnum;
use App\Enum\UserRoleEnum;
use App\Repository\VaultFolderRepository;
use App\Serializer\VaultFolderSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/vault/folders', name: 'vault_folders')]
#[IsGranted(UserRoleEnum::User->value)]
final class VaultFolderController extends AbstractController
{
    use ApiResponseTrait;
    use VaultOwnershipTrait;

    public function __construct(
        private readonly VaultFolderManagerInterface $folderManager,
        private readonly VaultFolderRepository $vaultFolderRepository,
        private readonly VaultFolderSerializer $serializer,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: '_list', methods: [HttpMethodEnum::Get->value])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'items' => array_map($this->serializer->serialize(...), $this->vaultFolderRepository->findByUser($user)),
        ]);
    }

    #[Route('', name: '_create', methods: [HttpMethodEnum::Post->value])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $input = VaultFolderInput::fromArray(json_decode($request->getContent(), true) ?? []);

        if (($response = $this->validateInput($input)) instanceof JsonResponse) {
            return $response;
        }

        $folder = $this->folderManager->create($user, $input);

        return $this->apiSuccess(['folder' => $this->serializer->serialize($folder)]);
    }

    #[Route('/{id}', name: '_update', methods: [HttpMethodEnum::Patch->value])]
    public function update(int $id, Request $request): JsonResponse
    {
        return $this->withOwned($id, $this->vaultFolderRepository->findByIdAndUser(...), function (VaultFolder $folder) use ($request): JsonResponse {
            $input = VaultFolderInput::fromArray(json_decode($request->getContent(), true) ?? []);

            if (($response = $this->validateInput($input)) instanceof JsonResponse) {
                return $response;
            }

            $this->folderManager->update($folder, $input);

            return $this->apiSuccess(['folder' => $this->serializer->serialize($folder)]);
        });
    }

    #[Route('/{id}', name: '_delete', methods: [HttpMethodEnum::Delete->value])]
    public function delete(int $id): JsonResponse
    {
        return $this->withOwned($id, $this->vaultFolderRepository->findByIdAndUser(...), function (VaultFolder $folder): JsonResponse {
            $this->folderManager->delete($folder);

            return $this->apiSuccess();
        });
    }
}
