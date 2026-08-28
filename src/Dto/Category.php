<?php

declare(strict_types=1);

namespace App\Dto;

final class Category
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly int $articlesCount = 0,
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            slug: $row['slug'],
            name: $row['name'],
            description: $row['description'] ?: null,
            articlesCount: (int) ($row['articles_count'] ?? 0),
        );
    }

    /**
     * @return self[]
     */
    public static function fromRows(array $rows): array
    {
        return array_map(static fn (array $row): self => self::fromArray($row), $rows);
    }
}