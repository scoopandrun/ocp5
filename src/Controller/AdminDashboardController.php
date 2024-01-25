<?php

namespace App\Controller;

use App\Service\PostService;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postService = new PostService();

        $postCount = $postService->getPostCount(false);

        $stats = [
            "postCount" => $postCount,
        ];

        $this->response->sendHTML(
            $this->twig->render(
                "admin/dashboard.html.twig",
                compact("stats")
            )
        );
    }
}
