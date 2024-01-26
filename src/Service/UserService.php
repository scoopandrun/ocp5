<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Service\EmailService;

class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function makeUserObject(array $userData): User
    {
        $user = (new User())
            ->setId($userData["id"] ?? null)
            ->setName($userData["name"] ?? "")
            ->setEmail($userData["email"] ?? "")
            ->setPassword($userData["password"] ?? null)
            ->setIsAdmin($userData["admin"] ?? false)
            ->setCreatedAt($userData["createdAt"] ?? null);

        return $user;
    }

    public function checkData(array $data): array
    {
        $formResult = [
            "success" => false,
            "failure" => false,
            "errors" => [
                "nameMissing" => !$data["name"] || $data["name"] === "",
                "nameTooLong" => $data["name"] && mb_strlen($data["name"]) > 255,
                "emailMissing" => !$data["email"] || $data["email"] === "",
                "emailInvalid" => $data["email"] && !preg_match("/.*@.*\.[a-z]+/", $data["email"]),
                "emailAlreadyTaken" => $this->userRepository->checkEmailTaken($data["email"]),
            ],
        ];

        return $formResult;
    }

    /**
     * Get a single user based on its ID.
     * 
     * @param int $id ID of the user.
     */
    public function getUser(int $id): User | null
    {
        return $this->userRepository->getUser($id);
    }

    /**
     * Get the users.
     * 
     * @param int $pageNumber     Page number.
     * @param int $pageSize       Number of users to show on a page.
     * 
     * @return array<array-key, \App\Entity\User> 
     */
    public function getUsers(int $pageNumber, int $pageSize): array
    {
        return $this->userRepository->getUsers($pageNumber, $pageSize);
    }

    /**
     * Get the amount of users in the database.
     */
    public function getUserCount(): int
    {
        return $this->userRepository->getUserCount();
    }

    public function editUser(User $user): void
    {
        $this->userRepository->editUser($user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->deleteUser($id);
    }
    }
