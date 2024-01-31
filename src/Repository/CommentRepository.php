<?php

namespace App\Repository;

use App\Core\Exceptions\Server\DB\DBException;
use App\Entity\{Comment, User};

class CommentRepository extends Repository
{
    /**
     * Fetch a single comment based on its ID.
     * 
     * @param int  $id           ID of the comment.
     * @param bool $approvedOnly Optional. Fetch only if the comment is approved. Default = `true`.
     */
    public function getComment(int $id): Comment | null
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                c.postId,
                c.createdAt,
                c.updatedAt,
                u.id as authorId,
                u.name as authorName,
                c.approved,
                c.title,
                c.body
            FROM comments c
            LEFT JOIN users u ON u.id = c.author
            WHERE
                c.id = :id"
        );

        $req->execute(compact("id"));

        $commentRaw = $req->fetch();

        if (!$commentRaw) {
            return null;
        }

        $author = !is_null($commentRaw["authorId"]) ?
            (new User)
            ->setId($commentRaw["authorId"])
            ->setName($commentRaw["authorName"])
            : null;

        $comment = (new Comment)
            ->setId($id)
            ->setPostId($commentRaw["postId"])
            ->setCreatedAt($commentRaw["createdAt"])
            ->setUpdatedAt($commentRaw["updatedAt"])
            ->setAuthor($author)
            ->setIsApproved($commentRaw["approved"])
            ->setTitle($commentRaw["title"])
            ->setBody($commentRaw["body"]);

        return $comment;
    }

    /**
     * Fetch the comments of a blog post.
     * 
     * @param int  $postId       ID of the post to fetch the comments for.
     * @param bool $approvedOnly Optional. Fetch only approved comments. Default = `true`.
     * 
     * @return array<int, \App\Entity\Comment> 
     */
    public function getPostComments(int $postId, bool $approvedOnly = true): array
    {
        $db = $this->connection;

        $approvedOnlySql = $approvedOnly ? "AND approved = 1" : "";

        $req = $db->prepare(
            "SELECT
                c.id,
                c.title,
                c.body,
                u.id as authorId,
                u.name as authorName,
                c.approved,
                c.createdAt,
                c.updatedAt
            FROM comments c
            LEFT JOIN users u ON u.id = c.author
            WHERE c.postId = :postId
            $approvedOnlySql
            ORDER BY c.createdAt ASC"
        );

        $req->execute(compact("postId"));

        $commentsRaw = $req->fetchAll();

        if ($commentsRaw === false) {
            throw new DBException("Erreur lors de la récupération des commentaires.");
        }

        $comments = array_map(function ($commentRaw) use ($postId) {
            $author = !is_null($commentRaw["authorId"]) ?
                (new User)
                ->setId($commentRaw["authorId"])
                ->setName($commentRaw["authorName"])
                : null;

            $comment = (new Comment)
                ->setId($commentRaw["id"])
                ->setPostId($postId)
                ->setTitle($commentRaw["title"])
                ->setBody($commentRaw["body"])
                ->setAuthor($author)
                ->setIsApproved($commentRaw["approved"])
                ->setCreatedAt($commentRaw["createdAt"])
                ->setUpdatedAt($commentRaw["updatedAt"]);

            return $comment;
        }, $commentsRaw);

        $req->closeCursor();

        return $comments;
    }

    /**
     * Fetch the comments to be approved.
     * 
     * @return array<int, \App\Entity\Comment> 
     */
    public function getCommentsToApprove(): array
    {
        $db = $this->connection;

        $req = $db->prepare(
            "SELECT
                c.id,
                c.postId,
                c.title,
                c.body,
                u.id as authorId,
                u.name as authorName,
                c.createdAt,
                c.updatedAt
            FROM comments c
            LEFT JOIN posts p ON p.id = c.postId
            LEFT JOIN users u ON u.id = p.author
            WHERE approved = 0
            ORDER BY c.createdAt ASC"
        );

        $req->execute();

        $commentsRaw = $req->fetchAll();

        if ($commentsRaw === false) {
            throw new DBException("Erreur lors de la récupération des commentaires.");
        }

        $comments = array_map(function ($commentRaw) {
            $author = !is_null($commentRaw["authorId"]) ?
                (new User)
                ->setId($commentRaw["authorId"])
                ->setName($commentRaw["authorName"])
                : null;

            $comment = (new Comment)
                ->setId($commentRaw["id"])
                ->setPostId($commentRaw["postId"])
                ->setTitle($commentRaw["title"])
                ->setBody($commentRaw["body"])
                ->setAuthor($author)
                ->setCreatedAt($commentRaw["createdAt"])
                ->setUpdatedAt($commentRaw["updatedAt"]);

            return $comment;
        }, $commentsRaw);

        $req->closeCursor();

        return $comments;
    }

    /**
     * Get the amount of blog posts in the database.
     * 
     * @param bool $approvedOnly Optional. Count only published posts. Default = `true`.
     * @param ?int $postId       Optional. Count posts related to a blog post. Default = `null`.  
     *                           If set to `null`, count the comments for all posts.
     */
    public function getCommentCount(?int $postId = null): array|false
    {
        $db = $this->connection;

        $postSql = $postId ? "AND postId = :postId" : "";

        $req = $db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM comments WHERE 1 $postSql) as total,
                (SELECT COUNT(*) FROM comments WHERE approved = 1 $postSql) as approved,
                (SELECT COUNT(*) FROM comments WHERE approved = 0 $postSql) as notApproved
            "
        );

        $req->execute($postId ? compact("postId") : null);

        /** @var array|false */
        $count = $req->fetch();

        return $count;
    }

    /**
     * @return int ID of the newly created comment.
     */
    public function createComment(Comment $comment): int
    {
        $db = $this->connection;


        try {
            $db->beginTransaction();

            $req = $db->prepare(
                "INSERT INTO comments
                SET
                    postId = :postId,
                    author = :author,
                    title = :title,
                    body = :body,
                    approved = 0"
            );

            $req->execute([
                "postId" =>  $comment->getPostId(),
                "author" =>  $comment->getAuthor()->getId(),
                "title" => $comment->getTitle(),
                "body" =>  $comment->getBody(),
            ]);

            $lastInsertId = $db->lastInsertId();

            $db->commit();
        } catch (\PDOException $e) {
            $db->rollBack();
            throw new DBException("Erreur lors de la création du commentaire.", previous: $e);
        }

        return (int) $lastInsertId;
    }
}
