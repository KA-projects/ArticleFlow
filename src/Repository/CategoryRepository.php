<?php

namespace App\Repository;

use App\Database;

class CategoryRepository
{
    public function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT c.*, COUNT(ac.article_id) AS articles_count
             FROM categories c
             LEFT JOIN article_category ac ON ac.category_id = c.id
             WHERE c.slug = ?
             GROUP BY c.id'
        );
        $stmt->execute([$slug]);

        return $stmt->fetch() ?: null;
    }

    public function allWithArticles(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT c.id, c.slug, c.name, COUNT(ac.article_id) AS articles_count
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }
}