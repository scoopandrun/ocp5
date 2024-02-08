<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\{PostService, CategoryService};
use App\Entity\Post;

class PostManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $postService = new PostService();

        /** @var int $postCount Total number of posts. */
        $postCount = $postService->getPostCount(false);

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        $pageSize = max((int) ($this->request->query["limit"] ?? null), 0) ?: 10;

        // Show last page in case $pageNumber is too high
        if ($postCount < ($pageNumber * $pageSize)) {
            $pageNumber = max(ceil($postCount / $pageSize), 1);
        }

        $posts = $postService->getPostsSummaries($pageNumber, $pageSize, false);

        $paginationInfo = [
            "pageSize" => $pageSize,
            "currentPage" => $pageNumber,
            "previousPage" => max($pageNumber - 1, 1),
            "nextPage" => min($pageNumber + 1, max(ceil($postCount / $pageSize), 1)),
            "lastPage" => max(ceil($postCount / $pageSize), 1),
            "firstItem" => ($pageNumber - 1) * $pageSize + 1,
            "lastItem" => min($pageNumber * $pageSize, $postCount),
            "itemCount" => $postCount,
            "itemName" => "posts",
            "endpoint" => "/admin/posts",
        ];

        if ($this->request->acceptsJSON()) {
            return $this->response->setJSON(
                json_encode(
                    [
                        "posts" => array_map(fn (Post $post) => $post->toArray(), $posts),
                        "paginationInfo" => $paginationInfo,
                    ]
                )
            );
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/post-management.html.twig",
                [
                    "posts" => $posts,
                    "paginationInfo" => $paginationInfo,
                ]
            )
        );
    }

    public function showEditPage(?int $postId = null): HTTPResponse
    {
        $postService = new PostService();
        $categoryService = new CategoryService();

        $post = $postId ? $postService->getPost($postId, false) : null;
        $categories = $categoryService->getCategories(false);

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

        /** @var array */
        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkFormData($postData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = null;
            $categoryService = new CategoryService();
            $categories = $categoryService->getCategories();
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

        $post = $postService->makePostObject($postData);

        $postId = $postService->createPost($post);

        return $this->response->redirect("/admin/posts");
    }

    public function editPost(int $postId): HTTPResponse
    {
        $postService = new PostService();

        /** @var array */
        $postData = $this->request->body["post"] ?? [];

        $formResult = $postService->checkFormData($postData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $post = $postId ? $postService->getPost($postId, false) : null;
            $categoryService = new CategoryService();
            $categories = $categoryService->getCategories();
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/post-edit.html.twig",
                        compact("post", "categories", "formResult")
                    )
                );
        }

        $postData["id"] = $postId;

        $post = $postService->makePostObject($postData);

        $postService->editPost($post);

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
