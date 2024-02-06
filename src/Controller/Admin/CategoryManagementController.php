<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\CategoryService;

class CategoryManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $categoryService = new CategoryService();
        $categories = $categoryService->getCategories();

        return $this->response->setHTML(
            $this->twig->render(
                "admin/category-management.html.twig",
                compact("categories")
            )
        );
    }
}
