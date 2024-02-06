<?php

namespace App\Controller;

use App\Core\HTTP\HTTPResponse;
use App\Core\Security;
use App\Core\Exceptions\Client\Auth\UnauthorizedException;
use App\Core\Exceptions\Client\ClientException;
use App\Service\UserService;

class UserController extends Controller
{
    public function showAccountPage(): HTTPResponse
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        return $this->response->setHTML(
            $this->twig->render("front/user.html.twig", compact("user"))
        );
    }

    public function redirectToAccountPage(): HTTPResponse
    {
        return $this->response->redirect("/user");
    }

    public function showLoginPage(): HTTPResponse
    {
        // If the user is already connected, redirect to homepage
        if ($this->request->user) {
            return $this->response->redirect("/");
        }

        $_SESSION["referer"] = $_SERVER["HTTP_REFERER"] ?? null;

        // Do not set the referer if it is the password reset page
        if (str_contains($_SESSION["referer"] ?? "", "passwordReset")) {
            $_SESSION["referer"] = null;
        }

        return $this->response->setHTML(
            $this->twig->render("front/user-login.html.twig")
        );
    }

    public function showSignupPage(): HTTPResponse
    {
        // If the user is already connected, redirect to homepage
        if ($this->request->user) {
            return $this->response->redirect("/");
        }

        $_SESSION["referer"] = $_SERVER["HTTP_REFERER"] ?? null;

        // Do not set the referer if it is the password reset page
        if (str_contains($_SESSION["referer"] ?? "", "passwordReset")) {
            $_SESSION["referer"] = null;
        }

        return $this->response->setHTML(
            $this->twig->render("front/user-signup.html.twig")
        );
    }

    public function login(): HTTPResponse
    {
        $userService = new UserService();

        $credentials = $this->request->body["loginForm"] ?? [];

        $loginOK = $userService->login($credentials);

        if (!$loginOK) {
            Security::preventBruteforce();
            return $this->response
                ->setCode(401)
                ->setHTML(
                    $this->twig->render(
                        "front/user-login.html.twig",
                        [
                            "formResult" => [
                                "failure" => true,
                            ],
                        ]
                    )
                );
        }

        // Redirect to the previous page or the homepage if no referer
        return $this->response->redirect($_SESSION["referer"] ?? "/");
    }

    public function logout(): HTTPResponse
    {
        $userService = new UserService();

        $userService->logout();

        return $this->response->redirect("/");
    }

    public function createAccount(): HTTPResponse
    {
        $userService = new UserService();

        /** @var array */
        $userData = $this->request->body["signupForm"] ?? [];

        if (gettype($userData) !== "array") {
            $userData = [];
        }

        $formResult = $userService->checkUserFormData($userData, newAccount: true);

        if (in_array(true, array_values($formResult["errors"]))) {
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "front/user-signup.html.twig",
                        compact("formResult")
                    )
                );
        }

        $userData["password"] = $userData["new-password"];
        $user = $userService->makeUserObject($userData);

        $userId = $userService->createUser($user);

        $_SESSION["usedId"] = $userId;

        // Redirect to the previous page or the homepage if no referer
        $this->response->redirect($_SESSION["referer"] ?? "/", 303);
    }

    public function editAccount(): HTTPResponse
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        /** @var array */
        $userData = $this->request->body["user"] ?? [];

        if (gettype($userData) !== "array") {
            $userData = [];
        }

        $formResult = $userService->checkUserFormData($userData, $user);

        if (in_array(true, array_values($formResult["errors"]))) {
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "front/user.html.twig",
                        compact("user", "formResult")
                    )
                );
        }

        $userData["id"] = $user->getId();
        $userData["admin"] = $user->getIsAdmin();
        $userData["password"] = $userData["new-password"] ?? "";
        $userEdited = $userService->makeUserObject($userData);

        $success = $userService->editUser($userEdited, $user);

        $formResult["success"] = $success;
        $formResult["failure"] = !$success;

        $this->response->setHTML(
            $this->twig->render(
                "front/user.html.twig",
                [
                    "user" => $success ? $userEdited : $user,
                    "formResult" => $formResult,
                ]
            )
        );
    }

    public function showDeleteAccountConfirmation(): HTTPResponse
    {
        return $this->response
            ->setHTML(
                $this->twig->render(
                    "front/user.html.twig",
                    ["showDeleteAccountConfirmation" => true]
                )
            );
    }

    public function deleteAccount(): HTTPResponse
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        $success = $userService->deleteUser($user->getId());

        if (!$success) {
            return $this->setResponseWithSingleMessage(
                "front/user.html.twig",
                "deleteAccountFailure",
                "Erreur lors de la suppression du compte",
                500
            );
        } else {
            $userService->logout();
            return $this->response->redirect("/", 303);
        }
    }

    public function sendVerificationEmail(): HTTPResponse
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        if ($user->getEmailVerified()) {
            return $this->setResponseWithSingleMessage(
                "front/user.html.twig",
                "verificationEmailError",
                "L'adresse e-mail est déjà vérifiée.",
                400
            );
        }

        $userService = new UserService();

        $emailSent = $userService->sendVerificationEmail($user->getEmail());

        if ($emailSent) {
            $statusCode = 200;
            $messageTitle = "verificationEmailSuccess";
            $message = "L'email a été envoyé.";
        } else {
            $statusCode = 500;
            $messageTitle = "verificationEmailError";
            $message = "Une erreur est survenue. L'email n'a pas été envoyé.";
        }

        return $this->setResponseWithSingleMessage(
            "front/user.html.twig",
            $messageTitle,
            $message,
            $statusCode
        );
    }

    public function verifyEmail(string $token): HTTPResponse
    {
        $userService = new UserService();

        $success = $userService->verifyEmail($token);

        if (!$success) {
            throw new ClientException("Le jeton de vérification n'est pas valide");
        }

        return $this->response->setHTML(
            $this->twig->render("front/user-email-verified.html.twig")
        );
    }

    public function showPaswordResetAskEmailPage(): HTTPResponse
    {
        return $this->response->setHTML(
            $this->twig->render("front/user-reset-password-1-ask-email.html.twig")
        );
    }

    public function sendPasswordResetEmail(): HTTPResponse
    {
        $userService = new UserService();

        /** @var array */
        $formData = $this->request->body["passwordResetEmailForm"] ?? [];

        if (gettype($formData) !== "array") {
            $formData = [];
        }

        $formResult = $userService->checkPasswordResetEmailFormData($formData);

        if ($formResult["error"]) {
            return $this->setResponseWithSingleMessage(
                "front/user-reset-password-1-ask-email.html.twig",
                "passwordResetEmailError",
                $formResult["error"],
                400
            );
        }

        $emailSent = $userService->sendPasswordResetEmail($formData);

        if ($emailSent) {
            $statusCode = 200;
            $messageTitle = "passwordResetEmailSuccess";
            $message = "Si cette adresse est associée à un compte,
             vous recevrez un e-mail de réinitialisation de mot de passe.";
        } else {
            $statusCode = 500;
            $messageTitle = "passwordResetEmailError";
            $message = "Une erreur est survenue. L'email n'a pas été envoyé.";
        }

        return $this->setResponseWithSingleMessage(
            "front/user-reset-password-1-ask-email.html.twig",
            $messageTitle,
            $message,
            $statusCode
        );
    }

    public function showPaswordResetChangePasswordPage(string $token): HTTPResponse
    {
        $userService = new UserService();

        $tokenIsValid = $userService->verifyPasswordResetToken($token);

        if (!$tokenIsValid) {
            throw new ClientException("Le jeton n'est pas valide");
        }

        return $this->response->setHTML(
            $this->twig->render(
                "front/user-reset-password-2-change-password.html.twig"
            )
        );
    }

    public function resetPassword(string $token): HTTPResponse
    {
        $userService = new UserService();

        $tokenIsValid = $userService->verifyPasswordResetToken($token);

        if (!$tokenIsValid) {
            throw new ClientException("Le jeton n'est pas valide");
        }

        /** @var array */
        $formData = $this->request->body["passwordResetForm"] ?? [];

        if (gettype($formData) !== "array") {
            $formData = [];
        }

        $formResult = $userService->checkPasswordResetFormData($formData);

        if ($formResult["error"]) {
            return $this->setResponseWithSingleMessage(
                "front/user-reset-password-2-change-password.html.twig",
                "passwordResetError",
                $formResult["error"],
                400
            );
        }

        /** @var string */
        $newPassword = $formData["new-password"];

        $passwordHasBeenReset = $userService->resetPassword($token, $newPassword);

        if ($passwordHasBeenReset) {
            $statusCode = 200;
            $messageTitle = "passwordResetSuccess";
            $message = "Le mot de passe a été correctement changé.";
        } else {
            $statusCode = 500;
            $messageTitle = "passwordResetError";
            $message = "Une erreur est survenue. Le mot de passe n'a pas été changé.";
        }

        return $this->setResponseWithSingleMessage(
            "front/user-reset-password-2-change-password.html.twig",
            $messageTitle,
            $message,
            $statusCode
        );
    }
}
