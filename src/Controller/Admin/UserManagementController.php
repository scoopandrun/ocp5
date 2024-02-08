<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Core\Exceptions\Client\NotFoundException;
use App\Service\UserService;
use App\Entity\User;

class UserManagementController extends AdminController
{
    public function show(): HTTPResponse
    {
        $userService = new UserService();

        /** @var int $userCount Total number of users. */
        $userCount = $userService->getUserCount();

        /** @var int $pageNumber Defaults to `1` in case of inconsistency. */
        $pageNumber = max((int) ($this->request->query["page"] ?? null), 1);

        $pageSize = max((int) ($this->request->query["limit"] ?? null), 0) ?: 10;

        // Show last page in case $pageNumber is too high
        if ($userCount < ($pageNumber * $pageSize)) {
            $pageNumber = max(ceil($userCount / $pageSize), 1);
        }

        $users = $userService->getUsers($pageNumber, $pageSize);

        $paginationInfo = [
            "pageSize" => $pageSize,
            "currentPage" => $pageNumber,
            "previousPage" => max($pageNumber - 1, 1),
            "nextPage" => min($pageNumber + 1, max(ceil($userCount / $pageSize), 1)),
            "lastPage" => max(ceil($userCount / $pageSize), 1),
            "firstItem" => ($pageNumber - 1) * $pageSize + 1,
            "lastItem" => min($pageNumber * $pageSize, $userCount),
            "itemCount" => $userCount,
            "itemName" => "utilisateurs",
            "endpoint" => "/admin/users",
        ];

        if ($this->request->acceptsJSON()) {
            return $this->response->setJSON(
                json_encode(
                    [
                        "users" => array_map(fn (User $user) => $user->toArray(), $users),
                        "paginationInfo" => $paginationInfo,
                    ]
                )
            );
        }

        return $this->response->setHTML(
            $this->twig->render(
                "admin/user-management.html.twig",
                [
                    "users" => $users,
                    "paginationInfo" => $paginationInfo,
                ]
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
