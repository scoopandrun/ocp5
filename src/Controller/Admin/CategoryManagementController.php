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

    public function showEditPage(?int $categoryId = null): HTTPResponse
    {
        $categoryService = new CategoryService();

        $category = $categoryId ? $categoryService->getCategory($categoryId) : null;
        $categories = $categoryService->getCategories();

        if ($categoryId && !$category) {
            throw new NotFoundException("La catégorie demandée n'existe pas");
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/category-edit.html.twig",
                compact("category")
            )
        );
    }

    public function createCategory(): HTTPResponse
    {
        $categoryService = new CategoryService();

        /** @var array */
        $categoryData = $this->request->body["category"] ?? [];

        $formResult = $categoryService->checkFormData($categoryData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $category = null;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/category-edit.html.twig",
                        compact("category", "formResult")
                    )
                );
        }

        $category = $categoryService->makeCategoryObject($categoryData);

        $categoryId = $categoryService->createCategory($category);

        if (!$categoryId) {
            $formResult["failure"] = true;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/category-edit.html.twig",
                        compact("category", "formResult")
                    )
                );
        }

        return $this->response->redirect("/admin/categories");
    }

    public function editCategory(int $categoryId): HTTPResponse
    {
        $categoryService = new CategoryService();

        $categoryData = $this->request->body["category"] ?? [];

        $formResult = $categoryService->checkFormData($categoryData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $category = $categoryId ? $categoryService->getCategory($categoryId, false) : null;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/category-edit.html.twig",
                        compact("category", "formResult")
                    )
                );
        }

        $categoryData["id"] = $categoryId;
        $category = $categoryService->makeCategoryObject($categoryData);

        $success = $categoryService->editCategory($category);

        if (!$success) {
            $formResult["failure"] = true;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/category-edit.html.twig",
                        compact("category", "formResult")
                    )
                );
        }

        return $this->response->redirect("/admin/categories");
    }
}
