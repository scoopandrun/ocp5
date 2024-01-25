<?php

namespace App\Controllers;

use App\Repositories\PostRepository;

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

        $this->twig->display("admin/dashboard.html.twig", compact("stats"));
    }
}
