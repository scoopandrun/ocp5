<?php

namespace App\Models;

use App\Core\DateTime;
use App\Models\{User, Category};

/**
 * A blog post.
 */
class Post
{
    public function __construct(
        public int $id,
        public DateTime $createdAt,
        public User $author,
        public Category $category,
        public string $title,
        public string $leadParagraph,
        public string $body = "",
    ) {
    }
}
