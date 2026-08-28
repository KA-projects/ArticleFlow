<?php

declare(strict_types=1);

namespace App\Contracts;

interface CategoryRepositoryInterface
{
    public function findBySlug(string $slug): ?array;

    public function allWithArticles(int $limit, int $offset): array;

    public function countWithArticles(): int;

    public function findById(int $id): ?array;
}