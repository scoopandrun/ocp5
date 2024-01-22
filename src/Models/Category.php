<?php

namespace App\Models;

class Category
{
    private ?int $id = null;
    private string $name = "Aucune catégorie";

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name ?? "Aucune catégorie";
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
