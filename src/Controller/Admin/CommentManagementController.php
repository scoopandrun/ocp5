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
}
