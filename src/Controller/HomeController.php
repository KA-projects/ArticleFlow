<?php

namespace App\Controller;

use Smarty;

class HomeController
{
    public function __construct(private Smarty $smarty)
    {
    }

    public function index(): void
    {
        $this->smarty->assign('pageTitle', 'Блог');
        $this->smarty->display('home.tpl');
    }
}