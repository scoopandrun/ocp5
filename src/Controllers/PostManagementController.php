<?php

namespace App\Controllers;

use App\Core\Exceptions\Client\NotFoundException;
use App\Repositories\{PostRepository, CategoryRepository};

class PostManagementController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postRepository = new PostRepository();
        $posts = $postRepository->getPostsSummaries(1, 100, false);

        $this->response->sendHTML(
            $this->twig->render(
                "admin/post-management.html.twig",
                compact("posts")
            )
        );
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

        $this->response->sendHTML(
            $this->twig->render(
                "admin/post-edit.html.twig",
                compact("post", "categories")
            )
        );
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
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postId = $postRepository->createPost($postData);

        header("Location: /admin/posts");
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
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postRepository->editPost($postId, $postData);

        header("Location: /admin/posts");
    }

    public function deletePost(int $postId): void
    {
        $postRepository = new PostRepository();

        $success = $postRepository->deletePost($postId);
    }
}
