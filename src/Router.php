<?php

namespace App;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $pattern = preg_replace('/\{slug\}/', '([a-z0-9-]+)', $pattern);
        $pattern = preg_replace('/\{id\}/', '(\d+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [$pattern, $handler];
    }

    public function dispatch(string $path, string $method): void
    {
        if ($method !== 'GET') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $path = rtrim($path, '/') ?: '/';
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as [$pattern, $handler]) {
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $handler(...$matches);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}