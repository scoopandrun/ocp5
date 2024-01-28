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

    public function checkUserFormData(
        array $data,
        ?User $user = null,
        bool $newAccount = false,
    ): array {
        $name = $data["name"] ?? "";
        $email = $data["email"] ?? "";
        $currentPassword = $data["current-password"] ?? "";
        $newPassword = $data["new-password"] ?? "";
        $passwordConfirm = $data["password-confirm"] ?? "";

        $formResult = [
            "success" => false,
            "failure" => false,
            "values" => compact("name", "email"),
            "errors" => [
                "nameMissing" => !$name,
                "nameTooLong" => mb_strlen($name) > 255,
                "emailMissing" => !$email,
                "emailInvalid" => !preg_match("/.*@.*\.[a-z]+/", $email),
                "emailAlreadyTaken" => $this->userRepository->checkEmailTaken($email, $user?->getId()),
                "currentPasswordMissing" => !$newAccount && $newPassword && !$currentPassword,
                "currentPasswordInvalid" =>
                !$newAccount
                    && $currentPassword
                    && $this->checkCredentials($user?->getEmail(), $currentPassword) === false,
                "newPasswordMissing" => $newAccount && !$newPassword,
                "passwordConfirmMissing" => $newPassword && !$passwordConfirm,
                "passwordMismatch" => $newPassword !== $passwordConfirm,
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
     * @param string|null $email    E-mail from the sign-in form.
     * @param string|null $password Password from the sign-in form.
     * 
     * @return int|false The user ID if the credentials are correct, `false` if the email OR password are incorrect.
     */
    public function checkCredentials(?string $email = null, ?string $password = null): int|false
    {
        if (!$email || !$password) {
            return false;
        }

        $user = $this->userRepository->getUserCredentials($email);

        if (!$user) {
            return false;
        }

        $passwordIsCorrect = password_verify($password, $user->getPassword() ?? "");

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

    /**
     * @return int The user id of the newly created user.
     */
    public function createUser(User $user): int
    {
        $userId = $this->userRepository->createUser($user);

        if (!$userId) {
            return false;
        }

        // Note: wait after the DB change is successful to send the verification e-mail
        $emailSent = $this->sendEmailVerificationEmail($user->getEmail());

        return $userId;
    }

    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function editUser(User $user): bool
    {
        return $this->userRepository->editUser($user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->deleteUser($id);
    }
    }
