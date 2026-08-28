<?php

namespace App\Controller;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Service\PaginationService;
use Smarty;

class CategoryController
{
    private const SORT_WHITELIST = ['views', 'date'];
    private const PER_PAGE = 6;

    public function __construct(
        private Smarty $smarty,
        private CategoryRepositoryInterface $categories,
        private ArticleRepositoryInterface $articles,
        private PaginationService $pagination,
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

        $requestedPage = max(1, (int) ($_GET['page'] ?? 1));
        $count = $this->articles->countByCategory($category->id);
        $pagination = $this->pagination->resolve($requestedPage, $count, self::PER_PAGE);
        $page = $pagination['page'];
        $totalPages = $pagination['totalPages'];
        $offset = $pagination['offset'];

        $articles = $this->articles->findByCategory($category->id, $sort, self::PER_PAGE, $offset);

        $baseUrl = '/category/' . $category->slug;

        $this->smarty->assign('pageTitle', $category->name);
        $this->smarty->assign('category', $category);
        $this->smarty->assign('articles', $articles);
        $this->smarty->assign('sort', $sort);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('totalPages', $totalPages);
        $this->smarty->assign('pages', $this->pagination->pageWindow($page, $totalPages));
        $this->smarty->assign('baseUrl', $baseUrl);
        $this->smarty->display('category.tpl');
    }
}
