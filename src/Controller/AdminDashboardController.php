<?php

namespace App\Controller;

use App\Repository\PostRepository;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(): void
    {
        $postRespository = new PostRepository();

        $postCount = $postRespository->getPostCount(false);

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
