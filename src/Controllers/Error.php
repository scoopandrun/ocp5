<?php

namespace App\Controllers;

class Error extends Controller
{
    public function __construct(protected \Throwable $e)
    {
        parent::__construct();
    }

    public function show()
    {
        $this->twig->display("error.html.twig", [
            "error" => $this->e
        ]);
    }
}
