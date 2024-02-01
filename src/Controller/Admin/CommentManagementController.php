<?php

namespace App\Controller\Admin;

use App\Core\Exceptions\Client\NotFoundException;
use App\Service\{PostService, CommentService};

class CommentManagementController extends AdminController
{
    public function show(): void
    {
        $commentService = new CommentService();
        $comments = $commentService->getCommentsToApprove();

        $this->response->sendHTML(
            $this->twig->render(
                "admin/comment-management.html.twig",
                compact("comments")
            )
        );
    }

    public function showReviewPage(int $id): void
    {
        $commentService = new CommentService();
        $comment = $commentService->getComment($id);

        if (!$comment) {
            throw new NotFoundException("Le commentaire n'existe pas");
        }

        $postService = new PostService();
        $postId = $comment->getPostId();
        $post = $postService->getPost($postId);

        $this->response->sendHTML(
            $this->twig->render(
                "admin/comment-review.html.twig",
                compact("comment", "post")
            )
        );
    }

    public function approveComment(int $id): void
    {
        $commentService = new CommentService();

        $success = $commentService->approveComment($id);

        $this->response->redirect("/admin/comments", 303);
    }

    public function rejectComment(int $id): void
    {
        $commentService = new CommentService();
        $comment = $commentService->getComment($id);

        if (!$comment) {
            throw new NotFoundException("Le commentaire n'existe pas");
        }

        $formData = $this->request->body["commentReview"] ?? [];

        if (!is_array($formData)) {
            $formData = [];
        }

        $formResult = $commentService->checkCommentReviewFormData($formData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $postService = new PostService();
            $postId = $comment->getPostId();
            $post = $postService->getPost($postId);

            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "admin/comment-review.html.twig",
                        compact("comment", "post", "formResult")
                    )
                );
            return;
        }

        $rejectReason = $formData["rejectReason"];

        $success = $commentService->rejectComment($id, $rejectReason);

        $formResult["success"] = $success;
        $formResult["failure"] = !$success;

        if (!$success) {
            $this->response
                ->setCode(500)
                ->sendHTML(
                    $this->twig->render(
                        "admin/comment-review.html.twig",
                        compact("comment", "post", "formResult")
                    )
                );
        } else {
            $this->response->redirect("/admin/comments", 303);
        }
    }
}
