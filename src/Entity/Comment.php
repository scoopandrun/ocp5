<?php

namespace App\Entity;

use App\Core\DateTime;
use App\Entity\User;

/**
 * A blog post comment.
 */
class Comment
{
    private ?int $id = null;
    private ?int $postId = null;
    private DateTime $createdAt;
    private ?User $author = null;
    private string $title = "";
    private string $body = "";
    private bool $isApproved = false;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): static
    {
        $this->postId = $postId;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime|string $createdAt): static
    {
        if (gettype($createdAt) === "string") {
            $this->createdAt = new DateTime($createdAt);
        } elseif ($createdAt::class === DateTime::class) {
            $this->createdAt = $createdAt;
        }

        return $this;
    }

    public function getAuthor(): User|null
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getIsApproved(): bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool|int $isApproved): static
    {
        $this->isApproved = (bool) $isApproved;
        return $this;
    }
}
