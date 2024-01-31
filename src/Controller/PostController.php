<?php

namespace App\Controller;

use App\Core\Exceptions\Client\NotFoundException;
use App\Service\PostService;

class PostController extends Controller
{
    public function showAll(): void
    {
        $postService = new PostService();

        /** @var int $postCount Total number of posts. */
        $postCount = $postService->getPostCount();

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        /** @var int $pageSize Number of posts to show on the page. */
        $pageSize = 10;

        // Show last page in case $pageNumber is too high
        if ($postCount < ($pageNumber * $pageSize)) {
            $pageNumber = ceil($postCount / $pageSize);
        }

        $posts = $postService->getPostsSummaries($pageNumber, $pageSize);

        $this->response
            ->sendHTML(
                $this->twig->render(
                    "front/post-archive.html.twig",
                    [
                        "posts" => $posts,
                        "page" => $pageNumber,
                        "previousPage" => $pageNumber > 1,
                        "nextPage" => $postCount > ($pageNumber * $pageSize),
                    ]
                )
            );
    }

    public function showOne(int $postId): void
    {
        $postService = new PostService();

        $post = $postService->getPost($postId);

        if (!$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        $this->response
            ->sendHTML(
                $this->twig->render(
                    "front/post-single.html.twig",
                    compact("post")
                )
            );
    }
}
