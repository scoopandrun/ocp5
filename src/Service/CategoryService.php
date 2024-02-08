<?php

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Entity\Category;

class CategoryService
{
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
    }

    public function makeCategoryObject(array $categoryData): Category
    {
        $category = (new Category())
            ->setId($categoryData["id"] ?? null)
            ->setName($categoryData["name"] ?? "");

        return $category;
    }

    /**
     * Get all categories.
     * 
     * @param int $pageNumber Page number.
     * @param int $pageSize   Number of blog posts to show on a page.
     * @param bool $withCount Also fetch the number of blog posts for each category.
     * 
     * @return array<int, \App\Entity\Category>
     */
    public function getCategories(
        int $pageNumber = 1,
        int $pageSize = 9999,
        bool $withCount = false
    ): array {
        return $this->categoryRepository->getCategories($pageNumber, $pageSize, $withCount);
    }

    /**
     * Get a single category based on its ID.
     * 
     * @param int $id ID of the category.
     */
    public function getCategory(int $id): Category|null
    {
        return $this->categoryRepository->getCategory($id);
    }

    public function getCategoryCount(): int
    {
        return $this->categoryRepository->getCategoryCount();
    }

    public function checkFormData(array $formData): array
    {
        $nameIsString = is_string($formData["name"] ?? null);

        $name = $nameIsString ? $formData["name"] : "";

        $nameMissing = $name === "";
        $nameTooLong = mb_strlen($name) > 20;
        $nameAlreadyExists = $this->categoryRepository->nameAlreadyExists($name);

        $formResult = [
            "success" => false,
            "failure" => false,
            "values" => compact(
                "name",
            ),
            "errors" => compact(
                "nameMissing",
                "nameTooLong",
                "nameAlreadyExists"
            ),
        ];

        return $formResult;
    }

    /**
     * @return int|false ID of the newly created category, or `false` on failure.
     */
    public function createCategory(Category $category): int|false
    {
        $this->sanitizeCategory($category);

        $lastIdOrFalse = $this->categoryRepository->createCategory($category);

        return $lastIdOrFalse;
    }

    public function editCategory(Category $category): bool
    {
        $this->sanitizeCategory($category);

        $success = $this->categoryRepository->editCategory($category);

        return $success;
    }

    private function sanitizeCategory(Category $category): void
    {
        $safeName = trim(htmlspecialchars($category->getName(), ENT_NOQUOTES));

        $category->setName($safeName);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->deleteCategory($id);
    }
}
