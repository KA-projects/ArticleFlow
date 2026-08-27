<?php

namespace App\Repository;

use App\Database;
use PDO;

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

    public function allWithArticles(int $limit, int $offset): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT c.id, c.slug, c.name, c.description, COUNT(ac.article_id) AS articles_count
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name
             LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countWithArticles(): int
    {
        $stmt = Database::getConnection()->query(
            'SELECT COUNT(*)
             FROM (
                 SELECT c.id
                 FROM categories c
                 INNER JOIN article_category ac ON ac.category_id = c.id
                 GROUP BY c.id
             ) t'
        );

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }
}