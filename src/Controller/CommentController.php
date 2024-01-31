<?php

namespace App\Controller;

use App\Service\{CommentService, PostService, UserService};
use App\Core\Exceptions\Client\NotFoundException;
use App\Core\Exceptions\Client\Auth\UnauthorizedException;
use App\Core\Exceptions\Client\Auth\ForbiddenException;

class CommentController extends Controller
{
    public function redirectToPostPage(int $postId): void
    {
        $this->response->redirect("/posts/$postId");
    }

    public function createComment(int $postId): void
    {
        $postService = new PostService();

        $post = $postService->getPost($postId);

        if (!$post) {
            throw new NotFoundException("Le post n'existe pas.");
        }

        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        if (!$user->getEmailVerified()) {
            throw new ForbiddenException(
                "Vous devez vérifier votre adresse e-mail pour poster des commentaires."
            );
        }

        $commentService = new CommentService();

        /** @var array */
        $commentData = $this->request->body["commentForm"] ?? [];

        if (gettype($commentData) !== "array") {
            $commentData = [];
        }

        $commentFormResult = $commentService->checkFormData($commentData);

        if (in_array(true, array_values($commentFormResult["errors"]))) {
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "front/post-single.html.twig",
                        compact("post", "commentFormResult")
                    )
                );
            return;
        }

        $commentData["postId"] = $postId;
        $commentData["author"] = $user;

        $comment = $commentService->makeCommentObject($commentData);

        $commentId = $commentService->createComment($comment);

        $commentFormResult["success"] = true;
        $commentFormResult["title"] = "";
        $commentFormResult["body"] = "";

        $this->response
            ->setCode(201)
            ->sendHTML(
                $this->twig->render(
                    "front/post-single.html.twig",
                    compact("post", "commentFormResult")
                )
            );
    }
}
