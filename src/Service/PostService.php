<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Entity\Post;

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
     * @param int $id             ID of the blog post.
     * @param bool $publishedOnly Optional. Fetch only if the post is published. Default = `true`.
     */
    public function getPost(int $id, bool $publishedOnly = true): Post | null
    {
        return $this->postRepository->getPost($id, $publishedOnly);
    }

    /**
     * Get the blog posts summaries.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of blog posts to show on a page.
     * @param bool $publishedOnly Optional. Fetch only published posts. Default = `true`.
     * 
     * @return array<array-key, \App\Entity\Post> 
     */
    public function getPostsSummaries(int $pageNumber, int $pageSize, bool $publishedOnly = true): array
    {
        return $this->postRepository->getPostsSummaries($pageNumber, $pageSize, $publishedOnly);
    }

    /**
     * Get the amount of blog posts in the database.
     * 
     * @param bool $publishedOnly Optional. Count only published posts. Default = `true`.
     */
    public function getPostCount(bool $publishedOnly = true): int
    {
        return $this->postRepository->getPostCount($publishedOnly);
    }

    public function checkData(array $data): array
    {
        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => [
                "titleMissing" => !$data["title"] || $data["title"] === "",
                "titleTooLong" => $data["title"] && mb_strlen($data["title"]) > 255,
                "leadParagraphTooLong" => $data["leadParagraph"] && mb_strlen($data["leadParagraph"]) > 255,
                "bodyTooLong" => $data["body"] && strlen($data["body"]) > pow(2, 16) + 2, // MySQL TEXT limit
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
}
