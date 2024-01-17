<?php

namespace App\Controllers;

use App\Repositories\PostRepository;

class HomepageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $postRepository = new PostRepository();
        $latestPosts = $postRepository->getPostsSummaries(1, 1);
        $this->twig->display("homepage.html.twig", compact("latestPosts"));
    }
}
