<?php

namespace App\Repository;

use App\Database;
use PDO;

class ArticleRepository
{
    private const SORT_WHITELIST = [
        'views' => 'a.views DESC',
        'date' => 'a.created_at DESC',
    ];

    public function latestByCategory(int $categoryId, int $limit = 3): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT a.id, a.slug, a.title, a.description, a.image, a.views, a.created_at
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByCategory(int $categoryId, string $sort, int $limit, int $offset): array
    {
        $orderBy = self::SORT_WHITELIST[$sort] ?? self::SORT_WHITELIST['date'];

        $stmt = Database::getConnection()->prepare(
            "SELECT a.id, a.slug, a.title, a.description, a.image, a.views, a.created_at
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*)
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = ?'
        );
        $stmt->execute([$categoryId]);

        return (int) $stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM articles WHERE slug = ?');
        $stmt->execute([$slug]);
        $article = $stmt->fetch();

        if ($article === false) {
            return null;
        }

        $categoryStmt = Database::getConnection()->prepare(
            'SELECT c.id, c.slug, c.name
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             WHERE ac.article_id = ?
             ORDER BY c.name'
        );
        $categoryStmt->execute([$article['id']]);

        $categories = $categoryStmt->fetchAll();
        $article['categories'] = $categories;
        $article['category_ids'] = array_column($categories, 'id');

        return $article;
    }

    public function incrementViews(int $id): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE articles SET views = views + 1 WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    public function similar(array $article, int $limit = 3): array
    {
        $categoryIds = $article['category_ids'] ?? [];

        if ($categoryIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $params = [(int) $article['id']];
        $params = array_merge($params, array_map('intval', $categoryIds));

        $stmt = Database::getConnection()->prepare(
            "SELECT DISTINCT a.id, a.slug, a.title, a.description, a.image, a.views, a.created_at
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE a.id <> ?
               AND ac.category_id IN ({$placeholders})
             LIMIT 50"
        );
        $stmt->execute($params);

        $candidates = $stmt->fetchAll();
        shuffle($candidates);

        return array_slice($candidates, 0, $limit);
    }
}