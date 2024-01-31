<?php

namespace App\Service;

use App\Repository\{CommentRepository, UserRepository};
use App\Entity\{Comment, User};

class CommentService
{
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->commentRepository = new CommentRepository();
        $this->userRepository = new UserRepository();
    }

    public function makeCommentObject(array $commentData): Comment
    {
        $author = $commentData["author"] ?
            ($commentData["author"] instanceof User
                ? $commentData["author"]
                : $this->userRepository->getAuthor($commentData["author"]))
            : null;

        $comment = (new Comment())
            ->setId($commentData["id"] ?? null)
            ->setPostId($commentData["postId"])
            ->setCreatedAt($commentData["createdAt"] ?? "now")
            ->setAuthor($author)
            ->setIsApproved($commentData["approved"] ?? false)
            ->setTitle($commentData["title"] ?? "")
            ->setBody($commentData["body"] ?? "");

        return $comment;
    }

    /**
     * Get a single blog post based on its ID.
     * 
     * @param int $id ID of the comment.
     */
    public function getComment(int $id): Comment | null
    {
        return $this->commentRepository->getComment($id);
    }

    /**
     * @return array<int, \App\Entity\Comment>
     */
    public function getPostComments(int $postId): array
    {
        return $this->commentRepository->getPostComments($postId);
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
        return $this->commentRepository->getCommentCount($postId);
    }

    public function checkFormData(array $formData): array
    {
        $titleIsString = gettype($formData["title"] ?? null) === "string";
        $bodyIsString = gettype($formData["body"] ?? null) === "string";

        $title = $titleIsString ? $formData["title"] : "";
        $body = $bodyIsString ? $formData["body"] : "";

        $titleMissing = $title === "";
        $titleTooLong = mb_strlen($title) > 255;
        $bodyMissing = $body === "";
        $bodyTooLong = strlen($body) > pow(2, 16) + 2; // MySQL TEXT limit;

        $formResult = [
            "success" => false,
            "failure" => false,
            "values" => compact(
                "title",
                "body",
            ),
            "errors" => compact(
                "titleMissing",
                "titleTooLong",
                "bodyMissing",
                "bodyTooLong",
            ),
        ];

        return $formResult;
    }

    /**
     * @return int ID of the newly created comment.
     */
    public function createComment(Comment $comment): int
    {
        return $this->commentRepository->createComment($comment);
    }

    /**
     * Get the comments to be approved.
     * 
     * @return array<int, \App\Entity\Comment>
     */
    public function getCommentsToApprove(): array
    {
        return $this->commentRepository->getCommentsToApprove();
    }
}
