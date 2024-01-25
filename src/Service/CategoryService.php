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

    /**
     * Get a single blog post based on its ID.
     * 
     * @param int $id             ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     * 
     * @return array<int, \App\Entity\Category>
     */
    public function getAll(): array
    {
        return $this->categoryRepository->getAll();
    }
}
