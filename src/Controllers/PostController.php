<?php

namespace App\Controllers;

use App\Core\Exceptions\Client\NotFoundException;
use App\Repositories\PostRepository;

class PostController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showAll()
    {
        $repository = new PostRepository;

        /** @var int $postCount Total number of posts. */
        $postCount = $repository->getPostCount();

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        /** @var int $pageSize Number of posts to show on the page. */
        $pageSize = 10;

        // Show last page in case $pageNumber is too high
        if ($postCount < ($pageNumber * $pageSize)) {
            $pageNumber = ceil($postCount / $pageSize);
        }

        $posts = $repository->getPostsSummaries($pageNumber, $pageSize);
        $this->twig->display(
            "post-archive.html.twig",
            [
                "posts" => $posts,
                "page" => $pageNumber,
                "previousPage" => $pageNumber > 1,
                "nextPage" => $postCount > ($pageNumber * $pageSize),
            ]
        );
    }

    public function showOne(int $postId)
    {
        $repository = new PostRepository;

        $post = $repository->getPost($postId);

        if (!$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        $this->twig->display("post-single.html.twig", compact("post"));
    }
}
