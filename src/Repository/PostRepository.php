<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Entity\{Post, User, Category};

class PostRepository extends Repository
{
    /**
     * Fetch a single blog post based on its ID.
     * 
     * @param int $id             ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     */
    public function getPost(int $id, bool $publishedOnly = true): Post | null
    {
        $db = $this->connection;

        $publishedOnlySql = $publishedOnly ? "AND p.published = 1" : "";

        $req = $db->prepare(
            "SELECT
                p.createdAt,
                p.updatedAt,
                u.id as authorId,
                u.name as authorName,
                c.id as categoryId,
                c.name as categoryName,
                p.title,
                p.leadParagraph,
                p.body,
                p.published,
                p.commentsAllowed
            FROM posts p
            LEFT JOIN users u ON u.id = p.author
            LEFT JOIN categories c ON c.id = p.category
            WHERE
                p.id = :id
                $publishedOnlySql"
        );

        $req->execute(compact("id"));

        $postRaw = $req->fetch();

        if (!$postRaw) {
            return null;
        }

        $author = !is_null($postRaw["authorId"]) ?
            (new User)
            ->setId($postRaw["authorId"])
            ->setName($postRaw["authorName"])
            : null;

        $category = !is_null($postRaw["categoryId"])
            ? (new Category)
            ->setId($postRaw["categoryId"])
            ->setName($postRaw["categoryName"])
            : null;

        $post = (new Post)
            ->setId($id)
            ->setCreatedAt($postRaw["createdAt"])
            ->setUpdatedAt($postRaw["updatedAt"])
            ->setAuthor($author)
            ->setCategory($category)
            ->setIsPublished($postRaw["published"])
            ->setCommentsAllowed($postRaw["commentsAllowed"])
            ->setTitle($postRaw["title"])
            ->setLeadParagraph($postRaw["leadParagraph"])
            ->setBody($postRaw["body"]);

        return $post;
    }

    /**
     * Fetch the blog posts summaries.
     * 
     * @param int   $pageNumber    Page number.
     * @param int   $pageSize      Number of blog posts to show on a page.
     * @param bool  $publishedOnly Optional. Fetch only published posts. Default = `true`.
     * @param array $categories    Optional. Array of category IDs to filter the posts.
     * @param array $authors       Optional. Array of author IDs to filter the posts.
     * 
     * @return array<int, \App\Entity\Post> 
     */
    public function getPostsSummaries(
        int $pageNumber,
        int $pageSize,
        bool $publishedOnly = true,
        array $categories = [],
        array $authors = [],
    ): array {
        $db = $this->connection;

        $publishedSql = $publishedOnly ? "AND p.published = 1" : "";
        $categoriesSql = empty($categories)
            ? ""
            : "AND p.category IN (" . join(",", $categories) . ")";
        $authorsSql = empty($authors)
            ? ""
            : "AND p.author IN (" . join(",", $authors) . ")";

        $req = $db->prepare(
            "SELECT
                p.id,
                p.createdAt,
                p.updatedAt,
                u.id as authorId,
                u.name as authorName,
                c.id as categoryId,
                c.name as categoryName,
                p.title,
                p.leadParagraph,
                p.published,
                p.commentsAllowed
            FROM posts p
            LEFT JOIN users u ON u.id = p.author
            LEFT JOIN categories c ON c.id = p.category
            WHERE 1
            $publishedSql
            $categoriesSql
            $authorsSql
            ORDER BY p.createdAt DESC
            LIMIT :limit
            OFFSET :offset"
        );

        $req->bindValue(":limit", $pageSize, \PDO::PARAM_INT);
        $req->bindValue(":offset", $pageSize * ($pageNumber - 1), \PDO::PARAM_INT);

        $req->execute();

        $postsRaw = $req->fetchAll();

        if ($postsRaw === false) {
            throw new DBException("Erreur lors de la récupération des posts.");
        }

        $posts = array_map(function ($postRaw) {
            $author = !is_null($postRaw["authorId"]) ?
                (new User)
                ->setId($postRaw["authorId"])
                ->setName($postRaw["authorName"])
                : null;

            $category = !is_null($postRaw["categoryId"])
                ? (new Category)
                ->setId($postRaw["categoryId"])
                ->setName($postRaw["categoryName"])
                : null;

            $post = (new Post)
                ->setId($postRaw["id"])
                ->setCreatedAt($postRaw["createdAt"])
                ->setUpdatedAt($postRaw["updatedAt"])
                ->setAuthor($author)
                ->setCategory($category)
                ->setIsPublished($postRaw["published"])
                ->setCommentsAllowed($postRaw["commentsAllowed"])
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
     * @param bool  $publishedOnly Optional. Count only published posts. Default = `true`.
     * @param array $categories    Optional. Array of category IDs to filter the post count.
     * @param array $authors       Optional. Array of author IDs to filter the post count.
     */
    public function getPostCount(
        bool $publishedOnly = true,
        array $categories = [],
        array $authors = [],
    ): int {
        $db = $this->connection;

        $publishedSql = $publishedOnly ? "AND published = 1" : "";
        $categoriesSql = empty($categories)
            ? ""
            : "AND category IN (" . join(",", $categories) . ")";
        $authorsSql = empty($authors)
            ? ""
            : "AND author IN (" . join(",", $authors) . ")";

        $req = $db->query(
            "SELECT COUNT(*)
            FROM posts
            WHERE 1
            $publishedSql
            $categoriesSql
            $authorsSql
            "
        );

        $count = $req->fetch(\PDO::FETCH_COLUMN);

        return $count;
    }

    public function createPost(array $data): int
    {
        $db = $this->connection;

        $db->beginTransaction();

        $req = $db->prepare(
            "INSERT INTO posts
            SET
                title = :title,
                leadParagraph = :leadParagraph,
                body = :body,
                author = :author,
                category = :category,
                published = :published,
                commentsAllowed = :commentsAllowed"
        );

        $req->execute([
            "title" => $data["title"],
            "leadParagraph" => $data["leadParagraph"],
            "body" => $data["body"],
            "author" => $data["author"],
            "category" => (int) ($data["category"] ?? null) ?: null,
            "published" => (int) isset($data["isPublished"]),
            "commentsAllowed" => (int) isset($data["commentsAllowed"]),
        ]);

        $lastInsertId = $db->lastInsertId();

        $db->commit();

        return (int) $lastInsertId;
    }

    public function editPost(int $id, array $data): void
    {
        $db = $this->connection;

        $req = $db->prepare(
            "UPDATE posts
            SET
                title = :title,
                leadParagraph = :leadParagraph,
                body = :body,
                category = :category,
                published = :published,
                commentsAllowed = :commentsAllowed,
                updatedAt = CURRENT_TIMESTAMP
            WHERE
                id = :id"
        );

        $req->execute([
            "id" => $id,
            "title" => $data["title"],
            "leadParagraph" => $data["leadParagraph"],
            "body" => $data["body"],
            "category" => (int) ($data["category"] ?? null) ?: null,
            "published" => (int) isset($data["isPublished"]),
            "commentsAllowed" => (int) isset($data["commentsAllowed"]),
        ]);
    }

    public function deletePost(int $id): bool
    {
        $db = $this->connection;

        $req = $db->prepare("DELETE FROM posts WHERE id = :id");

        $success = $req->execute(["id" => $id]);

        return $success;
    }
}
