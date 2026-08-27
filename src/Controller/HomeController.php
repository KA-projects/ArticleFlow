<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Smarty;

class HomeController
{
    public function __construct(
        private Smarty $smarty,
        private CategoryRepository $categories,
        private ArticleRepository $articles,
    ) {
    }

    public function index(): void
    {
        $sections = [];
        foreach ($this->categories->allWithArticles() as $category) {
            $sections[] = [
                'category' => $category,
                'articles' => $this->articles->latestByCategory((int) $category['id'], 3),
            ];
        }

        $this->smarty->assign('pageTitle', 'Блог');
        $this->smarty->assign('activeNav', 'home');
        $this->smarty->assign('sections', $sections);
        $this->smarty->display('home.tpl');
    }
}