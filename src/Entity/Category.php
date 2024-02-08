<?php

namespace App\Entity;

use App\Core\Interfaces\Arrayable;

class Category implements \Stringable, Arrayable
{
    private ?int $id = null;
    private string $name = "Aucune catégorie";
    private int $postCount = 0;

    public function __construct()
    {
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name ?? "Aucune catégorie";
        return $this;
    }

    public function getPostCount(): int
    {
        return $this->postCount;
    }

    public function setPostCount(int $postCount): static
    {
        $this->postCount = $postCount;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "postCount" => $this->postCount,
        ];
    }
}
