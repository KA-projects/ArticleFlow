<?php

namespace App\Controller;

use Smarty;

class ArticleController
{
    public function __construct(private Smarty $smarty)
    {
    }

    public function index(string $slug): void
    {
        $this->smarty->assign('pageTitle', 'Статья');
        $this->smarty->assign('slug', $slug);
        $this->smarty->display('article.tpl');
    }
}