<?php

namespace App\Controller\Admin;

use App\Core\Exceptions\Client\NotFoundException;
use App\Service\{PostService, CategoryService};

class PostManagementController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postService = new PostService();
        $posts = $postService->getPostsSummaries(1, 100, false);

        $this->response->sendHTML(
            $this->twig->render(
                "admin/post-management.html.twig",
                compact("posts")
            )
        );
    }

    public function showEditPage(?int $postId = null): void
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $post = $postId ? $postService->getPost($postId, false) : null;
        $categories = $categoryService->getAll();

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
        $postService = new PostService();
        $categoryService = new CategoryService();

        $categories = $categoryService->getAll();

        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkData($postData);

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

        $postId = $postService->createPost($postData);

        $this->response->redirect("/admin/posts");
    }

    public function editPost(int $postId): void
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $categories = $categoryService->getAll();

        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkData($postData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = $postId ? $postService->getPost($postId, false) : null;
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postService->editPost($postId, $postData);

        $this->response->redirect("/admin/posts");
    }

    public function deletePost(int $postId): void
    {
        $postService = new PostService();

        $success = $postService->deletePost($postId);

        if ($success) {
            $this->response->setCode(204)->send();
        }
    }
}
