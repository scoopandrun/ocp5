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
        $latestPosts = (new PostRepository)->getLatestPostsSummary();
        $this->twig->display("homepage.html.twig", compact("latestPosts"));
    }
}
