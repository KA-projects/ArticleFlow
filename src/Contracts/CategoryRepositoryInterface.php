<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Dto\Category;

interface CategoryRepositoryInterface
{
    public function findBySlug(string $slug): ?Category;

    /** @return Category[] */
    public function allWithArticles(int $limit, int $offset): array;

    public function countWithArticles(): int;

    public function findById(int $id): ?Category;
}