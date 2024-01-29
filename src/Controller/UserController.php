<?php

namespace App\Controller;

use App\Service\UserService;
use App\Core\Exceptions\Client\Auth\UnauthorizedException;
use App\Core\Exceptions\Client\ClientException;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showAccountPage(): void
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $this->response->sendHTML(
            $this->twig->render("front/user.html.twig", compact("user"))
        );
    }

    public function redirectToAccountPage(): void
    {
        $this->response->redirect("/user");
    }

    public function showLoginPage(): void
    {
        // If the user is already connected, redirect to homepage
        if ($this->request->user) {
            $this->response->redirect("/");
        }

        $_SESSION["referer"] = $_SERVER["HTTP_REFERER"] ?? null;

        $this->response->sendHTML(
            $this->twig->render("front/user-login.html.twig")
        );
    }

    public function showSignupPage(): void
    {
        // If the user is already connected, redirect to homepage
        if ($this->request->user) {
            $this->response->redirect("/");
        }

        $_SESSION["referer"] = $_SERVER["HTTP_REFERER"] ?? null;

        $this->response->sendHTML(
            $this->twig->render("front/user-signup.html.twig")
        );
    }

    public function login(): void
    {
        $userService = new UserService();

        $credentials = $this->request->body["loginForm"] ?? [];

        $loginOK = $userService->login($credentials);

        if (!$loginOK) {
            $this->response
                ->setCode(401)
                ->sendHTML(
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
        $this->response->redirect($_SESSION["referer"] ?? "/");
    }

    public function logout(): void
    {
        $userService = new UserService();

        $userService->logout();

        $this->response->redirect("/");
    }

    public function createAccount(): void
    {
        $userService = new UserService();

        /** @var array */
        $userData = $this->request->body["signupForm"] ?? [];

        if (gettype($userData) !== "array") {
            $userData = [];
        }

        $formResult = $userService->checkUserFormData($userData, newAccount: true);

        if (in_array(true, array_values($formResult["errors"]))) {
            $this->response
                ->setCode(400)
                ->sendHTML(
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

    public function editAccount(): void
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
            $this->response
                ->setCode(400)
                ->sendHTML(
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

        $this->response->sendHTML(
            $this->twig->render(
                "front/user.html.twig",
                [
                    "user" => $success ? $userEdited : $user,
                    "formResult" => $formResult,
                ]
            )
        );
    }

    public function showDeleteAccountConfirmation(): void
    {
        $this->response
            ->sendHTML(
                $this->twig->render(
                    "front/user.html.twig",
                    ["showDeleteAccountConfirmation" => true]
                )
            );
    }

    public function deleteAccount(): void
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        $success = $userService->deleteUser($user->getId());

        if (!$success) {
            $this->sendResponseWithSingleMessage(
                "front/user.html.twig",
                "deleteAccountFailure",
                "Erreur lors de la suppression du compte",
                500
            );
        } else {
            $userService->logout();
            $this->response->redirect("/", 303);
        }
    }

    public function sendVerificationEmail(): void
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        if ($user->getEmailVerified()) {
            $this->sendResponseWithSingleMessage(
                "front/user.html.twig",
                "verificationEmailError",
                "L'adresse e-mail est déjà vérifiée.",
                400
            );
        }

        $userService = new UserService();

        $emailSent = $userService->sendVerificationEmail($user->getEmail());

        if (!$emailSent) {
            $this->sendResponseWithSingleMessage(
                "front/user.html.twig",
                "verificationEmailError",
                "Une erreur est survenue. L'email n'a pas été envoyé.",
                500
            );
        } else {
            $this->sendResponseWithSingleMessage(
                "front/user.html.twig",
                "verificationEmailSuccess",
                "L'email a été envoyé."
            );
        }
    }

    public function verifyEmail(string $token): void
    {
        $userService = new UserService();

        $success = $userService->verifyEmail($token);

        if (!$success) {
            throw new ClientException("Le jeton de vérification n'est pas valide");
        }

        $this->response->sendHTML(
            $this->twig->render("front/user-email-verified.html.twig")
        );
    }
}
