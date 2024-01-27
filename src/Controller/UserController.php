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
            header("Location: /login");
        }

        $this->response->sendHTML(
            $this->twig->render("front/user.html.twig", compact("user"))
        );
    }

    public function editAccountInfo()
    {
        $user = $this->request->user;

        if (!$user?->getId()) {
            throw new UnauthorizedException();
        }

        $userService = new UserService();

        $userData = $this->request->body["user"] ?? [];

        $formResult = $userService->checkUserFormData($userData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "/user.html.twig",
                        compact("user", "formResult")
                    )
                );
        }

        $userData["id"] = $user->getId();
        $userData["admin"] = $user->getIsAdmin();
        $user = $userService->makeUserObject($userData);

        $userService->editUser($user);

        header("Location: /user");
    }

    public function showLoginPage()
    {
        $_SESSION["referer"] = $_SERVER["HTTP_REFERER"] ?? null;

        $this->response->sendHTML(
            $this->twig->render("front/user-login.html.twig")
        );
    }

    public function showSignupPage()
    {
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
            $this->response->sendHTML(
                $this->twig->render(
                    "front/user-login.html.twig",
                    [
                        "loginFormResult" => [
                            "failure" => true,
                        ],
                    ]
                )
            );
        }

        // Redirect to the previous page or the homepage if no referer
        header("Location: " . $_SESSION["referer"] ?? "/");
    }

    public function logout()
    {
        $userService = new UserService();

        $userService->logout();

        header("Location: /");
    }
}
