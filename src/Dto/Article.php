<?php

declare(strict_types=1);

namespace App\Dto;

final class Article
{
    /** @var Category[] */
    private array $categories = [];

    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $text,
        public readonly ?string $image,
        public readonly int $views,
        public readonly string $createdAt,
    ) {
    }

    /**
     * @param Category[] $categories
     */
    public function withCategories(array $categories): self
    {
        $copy = clone $this;
        $copy->categories = $categories;

        return $copy;
    }

    public function withIncrementedViews(): self
    {
        $copy = new self(
            id: $this->id,
            slug: $this->slug,
            title: $this->title,
            description: $this->description,
            text: $this->text,
            image: $this->image,
            views: $this->views + 1,
            createdAt: $this->createdAt,
        );
        $copy->categories = $this->categories;

        return $copy;
    }

    /**
     * @return Category[]
     */
    public function categories(): array
    {
        return $this->categories;
    }

    /**
     * @return int[]
     */
    public function categoryIds(): array
    {
        return array_map(static fn (Category $category): int => $category->id, $this->categories);
    }

    public static function fromArray(array $row, bool $withText = false): self
    {
        return new self(
            id: (int) $row['id'],
            slug: $row['slug'],
            title: $row['title'],
            description: $row['description'] ?: null,
            text: $withText ? ($row['text'] ?? null) : null,
            image: $row['image'] ?: null,
            views: (int) $row['views'],
            createdAt: $row['created_at'],
        );
    }

    /**
     * @return self[]
     */
    public static function fromRows(array $rows, bool $withText = false): array
    {
        return array_map(
            static fn (array $row): self => self::fromArray($row, $withText),
            $rows
        );
    }
}