<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Entity\Category;

class CategoryRepository extends Repository
{
    /**
     * Fetch all categories.
     * 
     * @param bool $withCount Also fetch the number of blog posts for each category.
     * 
     * @return array<int, \App\Entity\Category>
     */
    public function getCategories(bool $withCount = false): array
    {
        $db = $this->connection;

        $queryWithoutCount =
            "SELECT
                c.id,
                c.name
            FROM categories c
            ORDER BY c.name ASC";

        $queryWithCount =
            "SELECT
                c.id,
                c.name,
                COUNT(p.category) as postCount
            FROM categories c
            LEFT JOIN posts p ON c.id = p.category
            GROUP BY c.id
            UNION
            SELECT
                NULL as id,
                NULL as name,
                COUNT(IFNULL(p.category, 1)) as postCount
            FROM categories c
            RIGHT JOIN posts p ON c.id = p.category
            WHERE p.category IS NULL
            ORDER BY name ASC";

        $req = $db->query($withCount ? $queryWithCount : $queryWithoutCount);

        $categoriesRaw = $req->fetchAll();

        if (!$categoriesRaw) {
            throw new DBException("Erreur lors de la récupération des catégories.");
        }

        $categories = array_map(function ($categorieRaw) {
            $category = (new Category)
                ->setId($categorieRaw["id"])
                ->setName($categorieRaw["name"])
                ->setPostCount($categorieRaw["postCount"] ?? 0);

            return $category;
        }, $categoriesRaw);

        return $categories;
    }
}
