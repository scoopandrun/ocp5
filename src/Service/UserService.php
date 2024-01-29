<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Service\EmailService;
use Hidehalo\Nanoid\Client as Nanoid;

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
            ->setEmailVerificationToken($userData["emailVerificationToken"] ?? "")
            ->setEmailVerified($userData["emailVerified"] ?? false)
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
        $emailSent = $this->sendVerificationEmail($user->getEmail());

        return $userId;
    }

    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function editUser(User $userEdited, User $originalUser): bool
    {
        $emailChanged = $userEdited->getEmail() !== $originalUser->getEmail();

        if ($emailChanged) {
            $userEdited->setEmailVerified(false);
        }

        $dbSuccess = $this->userRepository->editUser($userEdited);

        if (!$dbSuccess) {
            return false;
        }

        // Note: wait after the DB change is successful to send the verification e-mail
        if ($emailChanged) {
            $emailSent = $this->sendVerificationEmail($userEdited->getEmail());
        }

        return $dbSuccess;
    }

    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->deleteUser($id);
    }

    private function generateToken(): string
    {
        return (new Nanoid())->generateId(21);
    }

    /**
     * Send an e-mail to verify the user's e-mail address.
     * 
     * @param string $email E-mail address to be verified.
     * 
     * @return bool `true` on success, `false` on failure.
     */
    public function sendVerificationEmail(string $email): bool
    {
        $emailService = new EmailService();

        $emailVerificationToken = $this->generateToken();

        $this->userRepository->setEmailVerificationToken($email, $emailVerificationToken);

        $emailBody = <<<HTML
            Bonjour,

            Cet e-mail automatique a été envoyé depuis le blog de Nicolas DENIS.
            
            Merci de vérifier votre adresse email en cliquant sur le lien ci-dessous :

            http://ocp5.local/user/verifyEmail/$emailVerificationToken

            Merci
            HTML;

        $emailService
            ->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"])
            ->addTo($email)
            ->setSubject("[OCP5] E-mail de vérification")
            ->setHTML(false)
            ->setBody($emailBody);

        return $emailService->send();
    }

    /**
     * Set an email as verified.
     * 
     * @param string $token 
     * 
     * @return bool `true` if the token is valid, `false` if the token is invalid
     */
    public function verifyEmail(string $token): bool
    {
        return $this->userRepository->verifyEmail($token);
    }

    public function checkPasswordResetEmailFormData(array $formData): array
    {
        $email = $formData["email"] ?? "";

        $emailIsString = gettype($email) === "string";

        $emailMissing = !$email || !$emailIsString;
        $emailInvalid = $emailIsString && !preg_match("/.*@.*\.[a-z]+/", $email);

        $errorMessage = $emailMissing
            ? "L'adresse e-mail est requise."
            : (
                $emailInvalid
                ? "L'adresse e-mail est invalide."
                : ""
            );

        $formResult = [
            "error" => $errorMessage,
        ];

        return $formResult;
    }

    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function sendPasswordResetEmail(array $formData): bool
    {
        /** @var string */
        $email = $formData["email"];

        $emailService = new EmailService();

        $passwordResetToken = $this->generateToken();

        $tokenIsSet = $this->userRepository->setPasswordResetToken($email, $passwordResetToken);

        // If an error uccroed during token setting, return false
        if ($tokenIsSet === false) {
            return false;
        }

        // If no error occured but the e-mail in unknown to the database
        // return true without sending the e-mail (=> obfuscation)
        if ($tokenIsSet === 0) {
            return true;
        }

        // If a token really has been set, send the e-mail

        $emailBody = <<<EMAIL
            Bonjour,

            Cet e-mail automatique a été envoyé depuis le blog de Nicolas DENIS.
            
            Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous :

            http://ocp5.local/passwordReset/$passwordResetToken

            Si vous n'avez pas demandé la réinitialisation de votre mot de passe,
            veuillez ignorer ce message.

            Merci
            EMAIL;

        $emailService
            ->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"])
            ->addTo($email)
            ->setSubject("[OCP5] Réinitialisation de mot de passe")
            ->setHTML(false)
            ->setBody($emailBody);

        return $emailService->send();
    }

    public function verifyPasswordResetToken(string $token): bool
    {
        return $this->userRepository->checkIfPasswordResetTokenIsRegistered($token);
    }


    public function checkPasswordResetFormData(array $formData): array
    {
        $newPassword = $formData["new-password"] ?? "";
        $passwordConfirm = $formData["password-confirm"] ?? "";

        $newPasswordIsString = gettype($newPassword) === "string";
        $passwordConfirmIsString = gettype($passwordConfirm) === "string";

        $newPasswordMissing = !$newPassword || !$newPasswordIsString;
        $passwordConfirmMissing = !$passwordConfirm || !$passwordConfirmIsString;
        $passwordMismatch = $newPassword !== $passwordConfirm;

        $errorMessage = "";

        switch (true) {
            case $newPasswordMissing:
                $errorMessage = "Le mot de passe est obligatoire.";
                break;

            case $passwordConfirmMissing:
                $errorMessage = "Le mot de passe doit être retapé.";

            case $passwordMismatch:
                $errorMessage = "Le mot de passe n'a pas été correctement retapé.";
                break;

            default:
                break;
        }

        $formResult = [
            "error" => $errorMessage,
        ];

        return $formResult;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $hashedPassword = (new User())->setPassword($password)->getPassword();

        return $this->userRepository->resetPassword($token, $hashedPassword);
    }
}
