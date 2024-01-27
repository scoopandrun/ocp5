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

    public function checkUserFormData(array $data): array
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
     * Check a user's credentials based on its ID.
     * 
     * @param string $email    E-mail from the sign-in form.
     * @param string $password Password from the sign-in form.
     * 
     * @return int|false The user ID if the credentials are correct, `false` if the email OR password are incorrect.
     */
    public function checkCredentials(string $email, string $password): int|false
    {
        $user = $this->userRepository->getUserCredentials($email);

        if (!$user) {
            return false;
        }

        $passwordIsCorrect = password_verify($password, $user->getPassword());

        if (!$passwordIsCorrect) {
            return false;
        }

        return $user->getId();
    }

    /**
     * Get the amount of users in the database.
     */
    public function getUserCount(): int
    {
        return $this->userRepository->getUserCount();
    }

    /**
     * Log the user in with credentials.
     * 
     * @param array $credentials Array with "email" and "password" keys.
     * @return int|null `true` if login OK, `false` otherwise.
     */
    public function login(array $credentials): bool
    {
        $email = $credentials["email"] ?? null;
        $password = $credentials["password"] ?? null;

        if (!$email || !$password) {
            return false;
        }

        $userId = $this->checkCredentials($email, $password);

        if (!$userId) {
            return false;
        }

        $_SESSION["userId"] = $userId;

        return true;
    }

    /**
     * Log a user out.
     */
    public function logout(): void
    {
        $cookieParams = session_get_cookie_params();

        // Clear session cookie
        setcookie(
            $_ENV["SESSION_COOKIE_NAME"],
            "",
            [
                "expires" => time() - 1,
                "path" => $cookieParams["path"],
                "httponly" => $cookieParams["httponly"],
            ]
        );
        session_unset();
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
