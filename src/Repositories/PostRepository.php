<?php

namespace App\Repositories;

use App\Core\Database\MySQLConnection;
use App\Core\DateTime;
use App\Core\Exceptions\Server\DB\DBException;
use App\Models\{Post, User, Category};

class PostRepository
{
    private MySQLConnection $connection;

    public function __construct(MySQLConnection $connection = new MySQLConnection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch the 3 latest blog posts.
     * 
     * @return array<array-key, \App\Models\Post> 
     */
    public function getLatestPostsSummary()
    {
        $db = $this->connection;

        // Fetch all the blog posts
        $req = $db->query(
            "SELECT
                p.id,
                p.createdAt,
                u.id as authorId,
                u.name as authorName,
                c.id as catId,
                c.name as catName,
                p.title,
                p.leadParagraph
            FROM posts p
            LEFT JOIN users u ON u.id = p.author
            LEFT JOIN categories c ON c.id = p.category
            WHERE p.published = 1
            ORDER BY p.createdAt DESC
            LIMIT 0, 3"
        );

        $postsRaw = $req->fetchAll();

        if (!$postsRaw) {
            throw new DBException("Erreur lors de la récupération des posts.");
        }

        $posts = array_map(function ($post) {
            return new Post(
                $post["id"],
                new DateTime($post["createdAt"]),
                new User($post["authorId"], $post["authorName"]),
                new Category($post["catId"], $post["catName"]),
                $post["title"],
                $post["leadParagraph"],
            );
        }, $postsRaw);

        $req->closeCursor();

        return $posts;
    }
}
