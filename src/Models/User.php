<?php

namespace App\Models;

use App\Core\DateTime;

class User implements \Stringable
{
    private ?int $id = null;
    private string $name = "Anonyme";
    private string $email = "";
    private string $password = "";
    private bool $isAdmin = false;
    private ?DateTime $createdAt = null;

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
        $this->name = $name ?? "Anonyme";
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    public function getAdmin()
    {
        return $this->isAdmin;
    }

    public function setAdmin(bool $isAdmin)
    {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
