<?php

namespace App\Controllers;

class HomepageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $this->twig->display("homepage.html.twig");
    }
}
