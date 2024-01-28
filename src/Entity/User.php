<?php

namespace App\Entity;

use App\Core\DateTime;

class User implements \Stringable
{
    private ?int $id = null;
    private string $name = "Anonyme";
    private string $email = "";
    private ?string $emailVerificationToken = null;
    private bool $emailVerified = false;
    private ?string $password = null;
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

    public function setName(string $name)
    {
        $this->name = $name ?: "Anonyme";
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

    public function getEmailVerificationToken()
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(string|null $emailVerificationToken)
    {
        $this->emailVerificationToken = $emailVerificationToken;
        return $this;
    }

    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool|int $emailVerified)
    {
        $this->emailVerified = (bool) $emailVerified;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(?string $password = null)
    {
        if (!$password) {
            $this->password = null;
        } else {
            $passwordIsHashed = password_get_info($password)["algo"] > 0;

            if (!$passwordIsHashed) {
                $this->password = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $this->password = $password;
            }
        }

        return $this;
    }

    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool|int $isAdmin)
    {
        $this->isAdmin = (bool) $isAdmin;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime|string|null $createdAt)
    {
        if (is_null($createdAt)) {
            $this->createdAt = null;
        } elseif (gettype($createdAt) === "string") {
            $this->createdAt = new DateTime($createdAt);
        } elseif (gettype($createdAt) === "object" && $createdAt::class === DateTime::class) {
            $this->createdAt = $createdAt;
        } else {
            $this->createdAt = null;
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
