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
        $safeTitle = htmlspecialchars($comment->getTitle(), ENT_NOQUOTES);
        $safeBody = htmlspecialchars($comment->getBody(), ENT_NOQUOTES);

        $comment
            ->setTitle($safeTitle)
            ->setBody($safeBody);

        return $this->commentRepository->createComment($comment);
    }

    /**
     * @return bool `true` if the comment was deleted, `false` otherwise.
     */
    public function deleteComment(int $id): bool
    {
        return $this->commentRepository->deleteComment($id);
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

    public function checkCommentReviewFormData(array $formData, string $approveOrReject = "reject"): array
    {
        if ($approveOrReject === "approve") {
            $errors = [];
        }

        if ($approveOrReject === "reject") {
            $rejectReason = is_string($formData["rejectReason"] ?? null) ? $formData["rejectReason"] : "";

            $errors = [
                "rejectReasonMissing" => !$rejectReason
            ];
        }

        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => $errors
        ];

        return $formResult;
    }

    public function approveComment(int $id): bool
    {
        return $this->commentRepository->approveComment($id);
    }

    public function rejectComment(int $id, string $reason): bool
    {
        $comment = $this->getComment($id);

        $postDeleted = $this->commentRepository->deleteComment($id);

        if (!$postDeleted) {
            return false;
        }

        // Send e-mail to user to explain why the comment was rejected
        $postService = new PostService();
        $post = $postService->getPost($comment->getPostId());

        $postTitle = $post->getTitle();
        $commentTitle = $comment->getTitle();
        $commentBody = $comment->getBody();

        $twig = (new TwigService())->getEnvironment();

        $emailService = new EmailService();

        $subject = "Commentaire rejeté";

        $emailBody = $emailService->createEmailBody(
            "reject-comment",
            $subject,
            compact(
                "reason",
                "postTitle",
                "commentTitle",
                "commentBody",
            )
        );

        $emailSent = $emailService
            ->addTo($comment->getAuthor()->getEmail())
            ->setSubject($subject)
            ->setBody($emailBody)
            ->send();

        return $emailSent;
    }
}
