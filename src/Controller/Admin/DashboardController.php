<?php

namespace App\Controller\Admin;

use App\Core\HTTP\HTTPResponse;
use App\Service\{PostService, UserService, CommentService};

class DashboardController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): HTTPResponse
    {
        $postService = new PostService();
        $userService = new UserService();
        $commentService = new CommentService();

        $postCount = $postService->getPostCount(false);
        $userCount = $userService->getUserCount();
        $commentCount = $commentService->getCommentCount();

        $stats = [
            "postCount" => $postCount,
            "userCount" => $userCount,
            "commentCount" => $commentCount,
        ];

        return $this->response->setHTML(
            $this->twig->render(
                "admin/dashboard.html.twig",
                compact("stats")
            )
        );
    }
}
