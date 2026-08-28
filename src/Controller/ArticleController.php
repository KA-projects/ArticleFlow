<?php

namespace App\Controller;

use App\Contracts\ArticleRepositoryInterface;
use Smarty;

class ArticleController
{
    public function __construct(
        private Smarty $smarty,
        private ArticleRepositoryInterface $articles,
    ) {
    }

    public function index(string $slug): void
    {
        $article = $this->articles->findBySlug($slug);

        if ($article === null) {
            http_response_code(404);
            $this->smarty->assign('pageTitle', 'Не найдено');
            $this->smarty->display('404.tpl');
            return;
        }

        $this->articles->incrementViews((int) $article['id']);
        $article['views'] = (int) $article['views'] + 1;

        $similar = $this->articles->similar($article, 3);

        $this->smarty->assign('pageTitle', $article['title']);
        $this->smarty->assign('article', $article);
        $this->smarty->assign('similar', $similar);
        $this->smarty->assign('viewsLabel', $this->plural((int) $article['views']));
        $this->smarty->display('article.tpl');
    }

    private function plural(int $n): string
    {
        $abs = abs($n) % 100;
        $last = $abs % 10;

        if ($abs >= 11 && $abs <= 19) {
            $word = 'просмотров';
        } elseif ($last === 1) {
            $word = 'просмотр';
        } elseif ($last >= 2 && $last <= 4) {
            $word = 'просмотра';
        } else {
            $word = 'просмотров';
        }

        return $n . ' ' . $word;
    }
}