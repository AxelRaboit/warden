<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

final readonly class AdminStatsService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'users' => [
                'total' => $this->userRepository->count([]),
            ],
        ];
    }
}
