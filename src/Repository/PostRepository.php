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
                p.published,
                p.title,
                p.leadParagraph,
                p.body
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
            ->setTitle($postRaw["title"])
            ->setLeadParagraph($postRaw["leadParagraph"])
            ->setBody($postRaw["body"]);

        return $post;
    }

    /**
     * Fetch the blog posts summaries.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of blog posts to show on a page.
     * @param bool $publishedOnly Optional. Fetch only published posts. Default = `true`.
     * 
     * @return array<int, \App\Entity\Post> 
     */
    public function getPostsSummaries(int $pageNumber, int $pageSize, bool $publishedOnly = true): array
    {
        $db = $this->connection;

        $publishedOnlySql = $publishedOnly ? "WHERE p.published = 1" : "";

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
                p.published
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
                published = :published"
        );

        $req->execute([
            "title" => $data["title"],
            "leadParagraph" => $data["leadParagraph"],
            "body" => $data["body"],
            "category" => (int) ($data["category"] ?? null) ?: null,
            "published" => (int) isset($data["isPublished"]),
            "author" => $data["author"],
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
