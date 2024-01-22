<?php

namespace App\Models;

use App\Core\DateTime;
use App\Models\{User, Category};

/**
 * A blog post.
 */
class Post
{
    private int $id;
    private DateTime $createdAt;
    private ?DateTime $updatedAt = null;
    private ?User $author = null;
    private ?Category $category = null;
    private string $title = "";
    private string $leadParagraph = "";
    private string $body = "";
    private bool $isPublished = true;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime|string $createdAt)
    {
        if (gettype($createdAt) === "string") {
            $this->createdAt = new DateTime($createdAt);
        } elseif ($createdAt::class === DateTime::class) {
            $this->createdAt = $createdAt;
        }

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime|string|null $updatedAt)
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

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(?User $author)
    {
        $this->author = $author;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(?Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function getLeadParagraph()
    {
        return $this->leadParagraph;
    }

    public function setLeadParagraph(string $leadParagraph)
    {
        $this->leadParagraph = $leadParagraph;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    public function getIsPublished()
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool|int $isPublished)
    {
        $this->isPublished = (bool) $isPublished;
        return $this;
    }
}
