<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\UserService;

class UserManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $userService = new UserService();
        $users = $userService->getUsers(1, 10);

        return $this->response->setHTML(
            $this->twig->render(
                "admin/user-management.html.twig",
                compact("users")
            )
        );
    }

    public function showEditPage(?int $userId = null): HTTPResponse
    {
        $userService = new UserService();

        $user = $userId ? $userService->getUser($userId, false) : null;

        if ($userId && !$user) {
            throw new NotFoundException("L'utilisateur n'existe pas");
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/user-edit.html.twig",
                compact("user")
            )
        );
    }

    public function editUser(int $userId): HTTPResponse
    {
        $userService = new UserService();

        $userData = $this->request->body["user"] ?? [];

        $formResult = $userService->checkUserFormData($userData);

        if (in_array(true, array_values($formResult["errors"]))) {
            $user = $userId ? $userService->getUser($userId) : null;
            return $this->response
                ->setCode(400)
                ->setHTML(
                    $this->twig->render(
                        "admin/user-edit.html.twig",
                        compact("user", "formResult")
                    )
                );
        }

        $userData["id"] = $userId;
        $userOriginal = $userService->getUser($userId);
        $userEdited = $userService->makeUserObject($userData);

        $userService->editUser($userEdited, $userOriginal);

        return $this->response->redirect("/admin/users");
    }

    public function deleteUser(int $userId): HTTPResponse
    {
        $userService = new UserService();

        $success = $userService->deleteUser($userId);

        if (!$success) {
            return $this->response->setCode(500);
        }

        return $this->response->setCode(204);
    }
}
