<?php

namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Core\HTTP\HTTPRequest;

abstract class Controller
{
    private FilesystemLoader $loader;
    protected Environment $twig;

    protected HTTPRequest $request;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(TEMPLATES);
        $this->twig = new Environment($this->loader);

        $this->request = new HTTPRequest;
    }
}
