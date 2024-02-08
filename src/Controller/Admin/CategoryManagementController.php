<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\CategoryService;
use App\Entity\Category;

class CategoryManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $categoryService = new CategoryService();

        /** @var int $categoryCount Total number of users. */
        $categoryCount = $categoryService->getCategoryCount() + 1; // Add 1 for the "no category line"

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        $pageSize = max((int) ($this->request->query["limit"] ?? null), 0) ?: 10;

        // Show last page in case $pageNumber is too high
        if ($categoryCount < ($pageNumber * $pageSize)) {
            $pageNumber = max(ceil($categoryCount / $pageSize), 1);
        }

        $categories = $categoryService->getCategories($pageNumber, $pageSize, true);

        $paginationInfo = [
            "pageSize" => $pageSize,
            "currentPage" => $pageNumber,
            "previousPage" => max($pageNumber - 1, 1),
            "nextPage" => min($pageNumber + 1, max(ceil($categoryCount / $pageSize), 1)),
            "lastPage" => max(ceil($categoryCount / $pageSize), 1),
            "firstItem" => ($pageNumber - 1) * $pageSize + 1,
            "lastItem" => min($pageNumber * $pageSize, $categoryCount),
            "itemCount" => $categoryCount,
            "itemName" => "catégories",
            "endpoint" => "/admin/categories",
        ];

        if ($this->request->acceptsJSON()) {
            return $this->response->setJSON(
                json_encode(
                    [
                        "categories" => array_map(fn (Category $category) => $category->toArray(), $categories),
                        "paginationInfo" => $paginationInfo,
                    ]
                )
            );
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/category-management.html.twig",
                [
                    "categories" => $categories,
                    "paginationInfo" => $paginationInfo,
                ]
            )
        );
    }

    public function showEditPage(?int $categoryId = null): HTTPResponse
    {
        $categoryService = new CategoryService();

        $category = $categoryId ? $categoryService->getCategory($categoryId) : null;

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

    public function deleteCategory(int $categoryId): HTTPResponse
    {
        $categoryService = new CategoryService();

        $success = $categoryService->deleteCategory($categoryId);

        if (!$success) {
            return $this->response->setCode(500);
        }

        return $this->response->setCode(204);
    }
}
