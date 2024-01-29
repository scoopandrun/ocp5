<?php

namespace App\Controller\Admin;

use App\Service\PostService;
use App\Service\UserService;

class DashboardController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postService = new PostService();
        $userService = new UserService();

        $postCount = $postService->getPostCount(false);
        $userCount = $userService->getUserCount();

        $stats = [
            "postCount" => $postCount,
            "userCount" => $userCount,
        ];

        $this->response->sendHTML(
            $this->twig->render(
                "admin/dashboard.html.twig",
                compact("stats")
            )
        );
    }
}
