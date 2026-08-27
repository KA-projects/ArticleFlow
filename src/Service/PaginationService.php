<?php

namespace App\Service;

class PaginationService
{
    public function pageWindow(int $page, int $totalPages): array
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