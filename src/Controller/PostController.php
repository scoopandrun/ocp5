<?php

namespace App\Controller;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\PostService;

class PostController extends Controller
{
    public function showAll(): HTTPResponse
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

        return $this->response
            ->setHTML(
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

    public function showOne(int $postId): HTTPResponse
    {
        $postService = new PostService();

        $post = $postService->getPost($postId);

        if (!$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        return $this->response
            ->setHTML(
                $this->twig->render(
                    "front/post-single.html.twig",
                    compact("post")
                )
            );
    }
}
