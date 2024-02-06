<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Entity\Post;
use App\Service\CommentService;

class PostService
{
    private PostRepository $postRepository;

    public function __construct()
    {
        $this->postRepository = new PostRepository();
    }

    /**
     * Get a single blog post based on its ID.
     * 
     * @param int  $id            ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     */
    public function getPost(int $id, bool $publishedOnly = true, bool $withComments = true): Post | null
    {
        $post = $this->postRepository->getPost($id, $publishedOnly);

        if ($post && $withComments) {
            $commentService = new CommentService();
            $comments = $commentService->getPostComments($id);
            $post->setComments($comments);
        }

        return $post;
    }

    /**
     * Get the blog posts summaries.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of blog posts to show on a page.
     * @param bool $publishedOnly Optional. Fetch only published posts. Default = `true`.
     * @param array $categories    Optional. Array of category IDs to filter the posts.
     * @param array $authors       Optional. Array of author IDs to filter the posts.
     * 
     * @return array<int, \App\Entity\Post> 
     */
    public function getPostsSummaries(
        int $pageNumber,
        int $pageSize,
        bool $publishedOnly = true,
        array $categories = [],
        array $authors = []
    ): array {
        return
            $this
            ->postRepository
            ->getPostsSummaries(
                $pageNumber,
                $pageSize,
                $publishedOnly,
                $categories,
                $authors
            );
    }

    /**
     * Get the amount of blog posts in the database.
     * 
     * @param bool  $publishedOnly Optional. Count only published posts. Default = `true`.
     * @param array $categories    Optional. Array of category IDs to filter the post count.
     * @param array $authors       Optional. Array of author IDs to filter the post count.
     */
    public function getPostCount(
        bool $publishedOnly = true,
        array $categories = [],
        array $authors = []
    ): int {
        return
            $this
            ->postRepository
            ->getPostCount(
                $publishedOnly,
                $categories,
                $authors
            );
    }

    public function checkFormData(array $formData): array
    {
        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => [
                "titleMissing" => !$formData["title"] || $formData["title"] === "",
                "titleTooLong" => $formData["title"] && mb_strlen($formData["title"]) > 255,
                "leadParagraphTooLong" => $formData["leadParagraph"] && mb_strlen($formData["leadParagraph"]) > 255,
                "bodyTooLong" => $formData["body"] && strlen($formData["body"]) > pow(2, 16) + 2, // MySQL TEXT limit
            ],
        ];

        return $formResult;
    }

    public function createPost(array $data): int
    {
        return $this->postRepository->createPost($data);
    }

    public function editPost(int $id, array $data): void
    {
        $this->postRepository->editPost($id, $data);
    }

    public function deletePost(int $id): bool
    {
        return $this->postRepository->deletePost($id);
    }

    public function parseQuery(array $query): array
    {
        $categories =
            $query["categories"] ?? null
            ? array_map(fn (mixed $category): int => (int) $category, explode(",", $query["categories"]))
            : [];
        $authors = $query["authors"] ?? null
            ? array_map(fn (mixed $author): int => (int) $author, explode(",", $query["authors"]))
            : [];

        return compact("categories", "authors");
    }
}
