<?php

declare(strict_types=1);

namespace App\Controller\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ApiResponseTrait
{
    use JsonValidationTrait;

    /**
     * @param array<string, mixed> $data
     */
    protected function apiSuccess(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return $this->json(['success' => true, ...$data], $status);
    }

    protected function apiNotFound(): JsonResponse
    {
        return $this->json(['success' => false], Response::HTTP_NOT_FOUND);
    }

    protected function apiError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->json(['success' => false, 'error' => $message], $status);
    }

    protected function apiValidationErrors(ConstraintViolationListInterface $violations): JsonResponse
    {
        return $this->json(['success' => false, 'errors' => $this->formatViolations($violations)]);
    }

    /**
     * @param array<string, string> $errors
     */
    protected function apiFieldErrors(array $errors): JsonResponse
    {
        return $this->json(['success' => false, 'errors' => $errors]);
    }

    /**
     * Validate a DTO and return a JsonResponse with errors if invalid, or null if valid.
     * Requires `$this->validator` (ValidatorInterface) to be injected.
     */
    protected function validateInput(object $input): ?JsonResponse
    {
        $violations = $this->validator->validate($input);

        return count($violations) > 0 ? $this->apiValidationErrors($violations) : null;
    }
}
