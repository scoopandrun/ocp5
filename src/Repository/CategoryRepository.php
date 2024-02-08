<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Core\ErrorLogger;
use App\Entity\Category;

class CategoryRepository extends Repository
{
    /**
     * Fetch all categories.
     * 
     * @param int $pageNumber Page number.
     * @param int $pageSize   Number of blog posts to show on a page.
     * @param bool $withCount Also fetch the number of blog posts for each category.
     * 
     * @return array<int, \App\Entity\Category>
     */
    public function getCategories(
        int $pageNumber,
        int $pageSize,
        bool $withCount = false
    ): array {
        $db = $this->connection;

        $queryWithoutCount =
            "SELECT
                c.id,
                c.name
            FROM categories c
            ORDER BY c.name ASC
            LIMIT :limit
            OFFSET :offset";

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
            ORDER BY name ASC
            LIMIT :limit
            OFFSET :offset";

        $req = $db->prepare($withCount ? $queryWithCount : $queryWithoutCount);

        $req->bindValue(":limit", $pageSize, \PDO::PARAM_INT);
        $req->bindValue(":offset", $pageSize * ($pageNumber - 1), \PDO::PARAM_INT);

        $req->execute();

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

    /**
     * Fetch a single category based on its ID.
     * 
     * @param int $id ID of the category.
     */
    public function getCategory(int $id): Category | null
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                c.id,
                c.name
            FROM categories c
            WHERE
                c.id = :id"
        );

        $req->execute(compact("id"));

        $categoryRaw = $req->fetch();

        if (!$categoryRaw) {
            return null;
        }

        $category = (new Category)
            ->setId($categoryRaw["id"])
            ->setName($categoryRaw["name"]);

        return $category;
    }

    /**
     * Get the amount of categories in the database.
     */
    public function getCategoryCount(): int
    {
        $db = $this->connection;

        $req = $db->query("SELECT COUNT(*) FROM categories");

        $count = $req->fetch(\PDO::FETCH_COLUMN);

        return $count;
    }

    public function createCategory(Category $category): int|false
    {
        $db = $this->connection;

        try {
            $db->beginTransaction();

            $req = $db->prepare("INSERT INTO categories SET name = :name");

            $req->execute([
                "name" => $category->getName(),
            ]);

            $lastInsertId = $db->lastInsertId();

            $db->commit();

            return (int) $lastInsertId;
        } catch (\PDOException $e) {
            $db->rollBack();
            (new ErrorLogger($e))->log();
            return false;
        }
    }

    public function editCategory(Category $category): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE categories
            SET
                name = :name
            WHERE
                id = :id"
        );

        $success = $req->execute([
            "id" => $category->getId(),
            "name" => $category->getName(),
        ]);

        return $success;
    }

    public function deleteCategory(int $id): bool
    {
        $db = $this->connection;

        $req = $db->prepare("DELETE FROM categories WHERE id = :id");

        $success = $req->execute(["id" => $id]);

        return $success;
    }

    /**
     * Check if a category already exists with the name.
     * 
     * @param string $name Category name to check.
     * 
     * @return bool `true` if a category already exists with that name, `false` otherwise.
     */
    public function nameAlreadyExists(string $name): bool
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT COUNT(*)
            FROM categories c
            WHERE c.name = :name"
        );

        $req->execute(compact("name"));

        $nameExists = (bool) $req->fetch(\PDO::FETCH_COLUMN);

        return $nameExists;
    }
}
