<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Entity\Category;

class CategoryRepository extends Repository
{
    /**
     * Fetch a single blog post based on its ID.
     * 
     * @param int $id             ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     * 
     * @return array<int, \App\Entity\Category>
     */
    public function getAll(): array
    {
        $db = $this->connection;

        $req = $db->query(
            "SELECT
                c.id,
                c.name
            FROM categories c"
        );


        $categoriesRaw = $req->fetchAll();

        if (!$categoriesRaw) {
            throw new DBException("Erreur lors de la récupération des posts.");
        }

        $categories = array_map(function ($categorieRaw) {
            $category = (new Category)
                ->setId($categorieRaw["id"])
                ->setName($categorieRaw["name"]);

            return $category;
        }, $categoriesRaw);

        return $categories;
    }
}
