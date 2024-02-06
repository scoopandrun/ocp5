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
}
