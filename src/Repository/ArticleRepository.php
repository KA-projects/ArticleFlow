<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\ArticleRepositoryInterface;
use App\Dto\Article;
use App\Dto\Category;
use PDO;

class ArticleRepository implements ArticleRepositoryInterface
{
    private const SORT_WHITELIST = [
        'views' => 'a.views DESC',
        'date' => 'a.created_at DESC',
    ];

    private const LIST_COLUMNS = 'a.id, a.slug, a.title, a.description, a.image, a.views, a.created_at';

    public function __construct(private PDO $pdo)
    {
    }

    public function latestByCategory(int $categoryId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT " . self::LIST_COLUMNS . "
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return Article::fromRows($stmt->fetchAll());
    }

    public function findByCategory(int $categoryId, string $sort, int $limit, int $offset): array
    {
        $orderBy = self::SORT_WHITELIST[$sort] ?? self::SORT_WHITELIST['date'];

        $stmt = $this->pdo->prepare(
            "SELECT " . self::LIST_COLUMNS . "
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

        return Article::fromRows($stmt->fetchAll());
    }

    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE ac.category_id = ?'
        );
        $stmt->execute([$categoryId]);

        return (int) $stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?Article
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        $article = Article::fromArray($row, withText: true);

        $categoryStmt = $this->pdo->prepare(
            'SELECT c.id, c.slug, c.name, c.description
             FROM categories c
             INNER JOIN article_category ac ON ac.category_id = c.id
             WHERE ac.article_id = ?
             ORDER BY c.name'
        );
        $categoryStmt->execute([$article->id]);

        $categories = Category::fromRows($categoryStmt->fetchAll());

        return $article->withCategories($categories);
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE articles SET views = views + 1 WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    public function similar(Article $article, int $limit = 3): array
    {
        $categoryIds = $article->categoryIds();

        if ($categoryIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $params = [$article->id];
        $params = array_merge($params, array_map('intval', $categoryIds));

        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT " . self::LIST_COLUMNS . "
             FROM articles a
             INNER JOIN article_category ac ON ac.article_id = a.id
             WHERE a.id <> ?
               AND ac.category_id IN ({$placeholders})
             LIMIT 50"
        );
        $stmt->execute($params);

        $candidates = Article::fromRows($stmt->fetchAll());
        shuffle($candidates);

        return array_slice($candidates, 0, $limit);
    }
}