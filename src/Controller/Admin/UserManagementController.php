<?php

namespace App\Controller\Admin;

use App\Core\Exceptions\Client\NotFoundException;
use App\Service\UserService;

class UserManagementController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $userService = new UserService();
        $users = $userService->getUsers(1, 10);

        $this->response->sendHTML(
            $this->twig->render(
                "admin/user-management.html.twig",
                compact("users")
            )
        );
    }

    public function showEditPage(?int $userId = null): void
    {
        $userService = new UserService();

        $user = $userId ? $userService->getUser($userId, false) : null;

        if ($userId && !$user) {
            throw new NotFoundException("L'utilisateur n'existe pas");
        }

        $this->response->sendHTML(
            $this->twig->render(
                "admin/user-edit.html.twig",
                compact("user")
            )
        );
    }

    public function editUser(int $userId): void
    {
        $userService = new UserService();

        $userData = $this->request->body["user"] ?? [];

        $formResult = $userService->checkUserFormData($userData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $user = $userId ? $userService->getUser($userId) : null;
            $this->response
                ->setCode(400)
                ->sendHTML(
                    $this->twig->render(
                        "admin/user-edit.html.twig",
                        compact("user", "formResult")
                    )
                );
        }

        $userData["id"] = $userId;
        $user = $userService->makeUserObject($userData);

        $userService->editUser($user);

        $this->response->redirect("/admin/users");
    }

    public function deleteUser(int $userId): void
    {
        $userService = new UserService();

        $success = $userService->deleteUser($userId);

        if ($success) {
            $this->response->setCode(204)->send();
        }
    }
}
