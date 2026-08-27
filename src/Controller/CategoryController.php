<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Smarty;

class CategoryController
{
    private const SORT_WHITELIST = ['views', 'date'];
    private const PER_PAGE = 6;

    public function __construct(
        private Smarty $smarty,
        private CategoryRepository $categories,
        private ArticleRepository $articles,
    ) {
    }

    public function index(string $slug): void
    {
        $category = $this->categories->findBySlug($slug);

        if ($category === null) {
            http_response_code(404);
            $this->smarty->assign('pageTitle', 'Не найдено');
            $this->smarty->display('404.tpl');
            return;
        }

        $sort = $_GET['sort'] ?? 'date';
        if (!in_array($sort, self::SORT_WHITELIST, true)) {
            $sort = 'date';
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $count = $this->articles->countByCategory((int) $category['id']);
        $totalPages = max(1, (int) ceil($count / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * self::PER_PAGE;
        $articles = $this->articles->findByCategory((int) $category['id'], $sort, self::PER_PAGE, $offset);

        $baseUrl = '/category/' . $category['slug'];

        $this->smarty->assign('pageTitle', $category['name']);
        $this->smarty->assign('category', $category);
        $this->smarty->assign('articles', $articles);
        $this->smarty->assign('sort', $sort);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('totalPages', $totalPages);
        $this->smarty->assign('pages', $this->pageWindow($page, $totalPages));
        $this->smarty->assign('baseUrl', $baseUrl);
        $this->smarty->display('category.tpl');
    }

    private function pageWindow(int $page, int $totalPages): array
    {
        $pages = [];

        for ($i = 1; $i <= $totalPages; $i++) {
            if (
                $i === 1 ||
                $i === $totalPages ||
                ($i >= $page - 2 && $i <= $page + 2)
            ) {
                $pages[] = ['page' => $i];
            } elseif (
                ($i === $page - 3 && $page - 3 > 1) ||
                ($i === $page + 3 && $page + 3 < $totalPages)
            ) {
                $pages[] = ['ellipsis' => true];
            }
        }

        return $pages;
    }
}
