<?php

namespace App\Controllers;

class ErrorController extends Controller
{
    public function __construct(protected \Throwable $e)
    {
        parent::__construct();
    }

    public function processRequest(): void
    {
    }

    public function show()
    {
        $this->twig->display("error.html.twig", [
            "error" => $this->e
        ]);
    }
}
