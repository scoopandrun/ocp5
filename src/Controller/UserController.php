<?php

namespace App\Controller;

use App\Service\UserService;
use App\Core\Exceptions\Client\Auth\UnauthorizedException;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function showAccountPage()
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $this->response->sendHTML(
            $this->twig->render("front/user.html.twig", compact("user"))
        );
    }

    public function redirectToAccountPage()
    {
        $this->response->redirect("/user");
    }

    public function showLoginPage()
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

    public function showSignupPage()
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

    public function login()
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

    public function logout()
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
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        $userData = $this->request->body["user"] ?? [];

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
        $user = $userService->makeUserObject($userData);

        $success = $userService->editUser($user);

        $formResult["success"] = $success;
        $formResult["failure"] = !$success;

        $this->response->sendHTML(
            $this->twig->render("front/user.html.twig", compact("user", "formResult"))
        );
    }

    public function showDeleteConfirmation()
    {
        $this->response
            ->sendHTML(
                $this->twig->render(
                    "front/user.html.twig",
                    ["showDeleteConfirmation" => true]
                )
            );
    }

    public function deleteAccount()
    {
        $user = $this->request->user;

        if (!$user) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        $success = $userService->deleteUser($user->getId());

        if (!$success) {
            $this->response->setCode(500);

            $errorMessage = "Erreur lors de la suppression";

            // HTML
            if ($this->request->acceptsHTML()) {
                $this->response
                    ->sendHTML(
                        $this->twig->render(
                            "front/user.html.twig",
                            [
                                "deleteFailure" => [
                                    "message" => $errorMessage
                                ]
                            ]
                        )
                    );
            }

            // JSON
            if ($this->request->acceptsJSON()) {
                $this->response
                    ->sendJSON(
                        json_encode(["message" => $errorMessage])
                    );
            }

            // Default
            $this->response->sendText($errorMessage);
        }

        if ($success) {
            $userService->logout();
            $this->response->redirect("/", 303);
        }
    }
}
