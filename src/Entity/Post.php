<?php

namespace App\Entity;

use App\Core\DateTime;
use App\Entity\{User, Category};

/**
 * A blog post.
 */
class Post
{
    private ?int $id = null;
    private DateTime $createdAt;
    private ?DateTime $updatedAt = null;
    private ?User $author = null;
    private ?Category $category = null;
    private string $title = "";
    private string $leadParagraph = "";
    private string $body = "";
    private bool $isPublished = true;
    private bool $commentsAllowed = true;
    /** @var array<int, \App\Entity\Comment> */
    private array $comments = [];

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
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

    public function getUpdatedAt(): DateTime|null
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime|string|null $updatedAt): static
    {
        if (is_null($updatedAt)) {
            $this->updatedAt = null;
        } elseif (gettype($updatedAt) === "string") {
            $this->updatedAt = new DateTime($updatedAt);
        } elseif (gettype($updatedAt) === "object" && $updatedAt::class === DateTime::class) {
            $this->updatedAt = $updatedAt;
        } else {
            $this->updatedAt = null;
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

    public function getCategory(): Category|null
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
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

    public function getLeadParagraph(): string
    {
        return $this->leadParagraph;
    }

    public function setLeadParagraph(string $leadParagraph): static
    {
        $this->leadParagraph = $leadParagraph;
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

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool|int $isPublished): static
    {
        $this->isPublished = (bool) $isPublished;
        return $this;
    }

    public function getCommentsAllowed(): bool
    {
        return $this->commentsAllowed;
    }

    public function setCommentsAllowed(bool|int $commentsAllowed): static
    {
        $this->commentsAllowed = (bool) $commentsAllowed;
        return $this;
    }

    /**
     * @return array<int, \App\Entity\Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array<int, \App\Entity\Comment> $comments
     */
    public function setComments(array $comments): static
    {
        $this->comments = $comments;
        return $this;
    }
}
