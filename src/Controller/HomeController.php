<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Service\PaginationService;
use Smarty;

class HomeController
{
    public function __construct(
        private Smarty $smarty,
        private CategoryRepository $categories,
        private ArticleRepository $articles,
        private PaginationService $pagination,
    ) {
    }

    private const PER_PAGE = 5;

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $count = $this->categories->countWithArticles();
        $totalPages = max(1, (int) ceil($count / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * self::PER_PAGE;
        $sections = [];
        foreach ($this->categories->allWithArticles(self::PER_PAGE, $offset) as $category) {
            $sections[] = [
                'category' => $category,
                'articles' => $this->articles->latestByCategory((int) $category['id'], 3),
            ];
        }

        $this->smarty->assign('pageTitle', 'Блог');
        $this->smarty->assign('activeNav', 'home');
        $this->smarty->assign('sections', $sections);
        $this->smarty->assign('sort', 'date');
        $this->smarty->assign('page', $page);
        $this->smarty->assign('totalPages', $totalPages);
        $this->smarty->assign('pages', $this->pagination->pageWindow($page, $totalPages));
        $this->smarty->assign('baseUrl', '/');
        $this->smarty->display('home.tpl');
    }
}