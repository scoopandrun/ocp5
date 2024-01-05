<?php

namespace App\Models;

use App\Core\DateTime;

class User
{
    public function __construct(
        public int $id = 0,
        public string $name = "Anonyme",
        public string $email = "",
        public string $password = "",
        public bool $isAdmin = false,
        public ?DateTime $createdAt = null,
    ) {
    }
}
