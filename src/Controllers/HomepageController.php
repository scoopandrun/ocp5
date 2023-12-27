<?php

namespace App\Controllers;

class HomepageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function processRequest(): void
    {
        switch ($this->request->method) {
            case 'GET':
                $this->show();
                break;

            default:
                # code...
                break;
        }
    }

    public function show()
    {
        $this->twig->display("homepage.html.twig");
    }
}
