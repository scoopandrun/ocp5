<?php

namespace App\Entity;

class Category
{
    private ?int $id = null;
    private string $name = "Aucune catégorie";

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

    public function __toString(): string
    {
        return $this->name;
    }
}
