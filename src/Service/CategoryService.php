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
     * @return array<int, \App\Entity\Category>
     */
    public function getCategories(): array
    {
        return $this->categoryRepository->getCategories(true);
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
        $safeName = trim(htmlspecialchars($category->getName(), ENT_NOQUOTES));

        $category->setName($safeName);

        $lastIdOrFalse = $this->categoryRepository->createCategory($category);

        return $lastIdOrFalse;
    }

    public function editCategory(Category $category): bool
    {
        $safeName = trim(htmlspecialchars($category->getName(), ENT_NOQUOTES));

        $category->setName($safeName);

        $success = $this->categoryRepository->editCategory($category);

        return $success;
    }
}
