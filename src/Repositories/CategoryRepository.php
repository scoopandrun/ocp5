<?php

namespace App\Repositories;

use App\Core\Database\MySQLConnection;
use App\Core\Exceptions\Server\DB\DBException;
use App\Models\Category;

class CategoryRepository
{
    private MySQLConnection $connection;

    public function __construct(MySQLConnection $connection = new MySQLConnection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch a single blog post based on its ID.
     * 
     * @param int $id             ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     * 
     * @return array<int, \App\Models\Category>
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
