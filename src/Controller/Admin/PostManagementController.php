<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\{PostService, CategoryService};

class PostManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $postService = new PostService();
        $posts = $postService->getPostsSummaries(1, 100, false);

        return $this->response->setHTML(
            $this->twig->render(
                "admin/post-management.html.twig",
                compact("posts")
            )
        );
    }

    public function showEditPage(?int $postId = null): HTTPResponse
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $post = $postId ? $postService->getPost($postId, false) : null;
        $categories = $categoryService->getAll();

        if ($postId && !$post) {
            throw new NotFoundException("Le post demandé n'existe pas");
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/post-edit.html.twig",
                compact("post", "categories")
            )
        );
    }

    public function createPost(): HTTPResponse
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $categories = $categoryService->getAll();

        /** @var array */
        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkFormData($postData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = null;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postData["author"] = $this->request->user->getId();

        $postId = $postService->createPost($postData);

        return $this->response->redirect("/admin/posts");
    }

    public function editPost(int $postId): HTTPResponse
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $categories = $categoryService->getAll();

        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkFormData($postData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = $postId ? $postService->getPost($postId, false) : null;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postService->editPost($postId, $postData);

        return $this->response->redirect("/admin/posts");
    }

    public function deletePost(int $postId): HTTPResponse
    {
        $postService = new PostService();

        $success = $postService->deletePost($postId);

        if (!$success) {
            return $this->response->setCode(500);
        }

        return $this->response->setCode(204);
    }
}
