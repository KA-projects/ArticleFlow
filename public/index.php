<?php

declare(strict_types=1);

use App\Controller\ArticleController;
use App\Controller\CategoryController;
use App\Controller\HomeController;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

$smarty = new \Smarty();
$smarty->setTemplateDir(dirname(__DIR__) . '/templates');
$smarty->setCompileDir('/tmp/smarty_compile');
$smarty->setCacheDir('/tmp/smarty_cache');
$smarty->setCaching(false);

$homeController = new HomeController($smarty, new CategoryRepository(), new ArticleRepository());
$categoryController = new CategoryController($smarty);
$articleController = new ArticleController($smarty);

$router = new Router();
$router->get('/', fn () => $homeController->index());
$router->get('/category/{slug}', fn (string $slug) => $categoryController->index($slug));
$router->get('/article/{slug}', fn (string $slug) => $articleController->index($slug));

try {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $router->dispatch($path, $_SERVER['REQUEST_METHOD']);
} catch (Throwable $e) {
    http_response_code(500);
    if ($config['debug']) {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
    } else {
        echo '500 Internal Server Error';
    }
}