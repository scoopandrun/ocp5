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
     * Fetch the blog posts summaries.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of blog posts to show on a page.
     * @param bool $publishedOnly Optional. Fetch only published posts. Default = `true`.
     * 
     * @return array<array-key, \App\Models\Post> 
     */
    public function getPostsSummaries(int $pageNumber, int $pageSize, bool $publishedOnly = true): array
    {
        $db = $this->connection;

        $publishedOnlySql = $publishedOnly ? "WHERE p.published = 1" : "";

        $req = $db->prepare(
            "SELECT
                p.id,
                p.createdAt,
                u.id as authorId,
                u.name as authorName,
                c.id as categoryId,
                c.name as categoryName,
                p.title,
                p.leadParagraph
            FROM posts p
            LEFT JOIN users u ON u.id = p.author
            LEFT JOIN categories c ON c.id = p.category
            $publishedOnlySql
            ORDER BY p.createdAt DESC
            LIMIT :limit
            OFFSET :offset"
        );

        $req->bindValue(":limit", $pageSize, \PDO::PARAM_INT);
        $req->bindValue(":offset", $pageSize * ($pageNumber - 1), \PDO::PARAM_INT);

        $req->execute();

        $postsRaw = $req->fetchAll();

        if (!$postsRaw) {
            throw new DBException("Erreur lors de la récupération des posts.");
        }

        $posts = array_map(function ($postRaw) {
            $author = (new User)
                ->setId($postRaw["authorId"])
                ->setName($postRaw["authorName"]);

            $category = (new Category)
                ->setId($postRaw["categoryId"])
                ->setName($postRaw["categoryName"]);

            $post = (new Post)
                ->setId($postRaw["id"])
                ->setCreatedAt(new DateTime($postRaw["createdAt"]))
                ->setAuthor($author)
                ->setCategory($category)
                ->setTitle($postRaw["title"])
                ->setLeadParagraph($postRaw["leadParagraph"]);

            return $post;
        }, $postsRaw);

        $req->closeCursor();

        return $posts;
    }

    /**
     * Get the amount of blog posts in the database.
     * 
     * @param bool $published Optional. Count only published posts. Default = `true`.
     */
    public function getPostCount(bool $published = true): int
    {
        $db = $this->connection;

        $whereClause = $published ? "WHERE published = 1" : "";

        $req = $db->query("SELECT COUNT(*) FROM posts $whereClause");

        $count = $req->fetch(\PDO::FETCH_COLUMN);

        return $count;
    }
}
