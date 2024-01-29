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

    public function setName(string $name): static
    {
        $this->name = $name ?: "Anonyme";
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getEmailVerificationToken(): string|null
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(string|null $emailVerificationToken): static
    {
        $this->emailVerificationToken = $emailVerificationToken;
        return $this;
    }

    public function getEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool|int $emailVerified): static
    {
        $this->emailVerified = (bool) $emailVerified;
        return $this;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    /**
     * @param null|string $password Hashed or clear password.  
     *                              If the password is not hashed,
     *                              the function will hash it.
     */
    public function setPassword(?string $password = null): static
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

    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool|int $isAdmin): static
    {
        $this->isAdmin = (bool) $isAdmin;
        return $this;
    }

    public function getCreatedAt(): DateTime|null
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime|string|null $createdAt): static
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
