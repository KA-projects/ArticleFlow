<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Dto\Article;

interface ArticleRepositoryInterface
{
    /** @return Article[] */
    public function latestByCategory(int $categoryId, int $limit = 3): array;

    /** @return Article[] */
    public function findByCategory(int $categoryId, string $sort, int $limit, int $offset): array;

    public function countByCategory(int $categoryId): int;

    public function findBySlug(string $slug): ?Article;

    public function incrementViews(int $id): void;

    /** @return Article[] */
    public function similar(Article $article, int $limit = 3): array;
}