<?php

namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Core\HTTP\HTTPRequest;
use App\Models\Model;

abstract class Controller implements IController
{
    private FilesystemLoader $loader;
    protected Environment $twig;

    protected HTTPRequest $request;
    protected Model $model;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(TEMPLATES);
        $this->twig = new Environment($this->loader);

        $this->request = new HTTPRequest;
    }
}

interface IController
{
    public function processRequest(): void;
}
