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

        /**
         * @var array $categories
         * @var array $authors
         */
        extract($postService->parseQuery($this->request->query));

        /** @var int $postCount Total number of posts. */
        $postCount = $postService->getPostCount(true, $categories, $authors);

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        /** @var int $pageSize Number of posts to show on the page. */
        $pageSize = 10;

        // Show last page in case $pageNumber is too high
        if ($postCount < ($pageNumber * $pageSize)) {
            $pageNumber = max(ceil($postCount / $pageSize), 1);
        }

        $posts = $postService->getPostsSummaries(
            $pageNumber,
            $pageSize,
            true,
            $categories,
            $authors
        );

        return $this->response
            ->setHTML(
                $this->twig->render(
                    "front/post-archive.html.twig",
                    [
                        "posts" => $posts,
                        "page" => $pageNumber,
                        "previousPage" => $pageNumber > 1,
                        "nextPage" => $postCount > ($pageNumber * $pageSize),
                        "categoriesQuery" =>
                        empty($categories)
                            ? ""
                            : "&categories=" . join(",", $categories),
                        "authorsQuery" =>
                        empty($authors)
                            ? ""
                            : "&authors=" . join(",", $authors),
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
