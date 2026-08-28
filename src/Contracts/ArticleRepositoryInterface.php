<?php

declare(strict_types=1);

namespace App\Contracts;

interface ArticleRepositoryInterface
{
    public function latestByCategory(int $categoryId, int $limit = 3): array;

    public function findByCategory(int $categoryId, string $sort, int $limit, int $offset): array;

    public function countByCategory(int $categoryId): int;

    public function findBySlug(string $slug): ?array;

    public function incrementViews(int $id): void;

    public function similar(array $article, int $limit = 3): array;
}