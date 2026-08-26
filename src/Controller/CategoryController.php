<?php

namespace App\Controller;

use Smarty;

class CategoryController
{
    public function __construct(private Smarty $smarty)
    {
    }

    public function index(string $slug): void
    {
        $this->smarty->assign('pageTitle', 'Категория');
        $this->smarty->assign('slug', $slug);
        $this->smarty->display('category.tpl');
    }
}