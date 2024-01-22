<?php

namespace App\Controllers;

use App\Core\Exceptions\Client\NotFoundException;
use App\Repositories\{PostRepository, CategoryRepository};

class PostController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showAll(): void
    {
        $repository = new PostRepository();

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

    public function showOne(int $postId): void
    {
        $postRepository = new PostRepository();

        $post = $postRepository->getPost($postId);

        if (!$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        $this->twig->display("post-single.html.twig", compact("post"));
    }

    public function showEditPage(?int $postId = null): void
    {
        $postRepository = new PostRepository();
        $categoryRepository = new CategoryRepository();

        $post = $postId ? $postRepository->getPost($postId, false) : null;
        $categories = $categoryRepository->getAll();

        if ($postId && !$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        $this->twig->display("post-edit.html.twig", compact("post", "categories"));
    }

    public function createPost(): void
    {
        $postRepository = new PostRepository();
        $categoryRepository = new CategoryRepository();

        $categories = $categoryRepository->getAll();

        $postData = $this->request->body["post"] ?? [];

        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => [
                "titleMissing" => !$postData["title"] || $postData["title"] === "",
                "titleTooLong" => $postData["title"] && mb_strlen($postData["title"]) > 255,
                "leadParagraphTooLong" => $postData["leadParagraph"] && mb_strlen($postData["leadParagraph"]) > 255,
                "bodyTooLong" => $postData["body"] && strlen($postData["body"]) > pow(2, 16) + 2, // MySQL TEXT limit
            ],
        ];

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = null;
            $this->twig->display("post-edit.html.twig", compact("post", "categories", "formResult"));
            exit;
        }

        $postId = $postRepository->createPost($postData);

        header("Location: /posts/$postId");
    }

    public function editPost(int $postId): void
    {
        $postRepository = new PostRepository();
        $categoryRepository = new CategoryRepository();

        $categories = $categoryRepository->getAll();

        $postData = $this->request->body["post"] ?? [];


        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => [
                "titleMissing" => !$postData["title"] || $postData["title"] === "",
                "titleTooLong" => $postData["title"] && mb_strlen($postData["title"]) > 255,
                "leadParagraphTooLong" => $postData["leadParagraph"] && mb_strlen($postData["leadParagraph"]) > 255,
                "bodyTooLong" => $postData["body"] && strlen($postData["body"]) > pow(2, 16) + 2, // MySQL TEXT limit
            ],
        ];

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = $postId ? $postRepository->getPost($postId, false) : null;
            $this->twig->display("post-edit.html.twig", compact("post", "categories", "formResult"));
            exit;
        }

        $postRepository->editPost($postId, $postData);

        header("Location: /posts/$postId");
    }
}
