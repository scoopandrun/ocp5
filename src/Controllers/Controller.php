<?php

namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\Markdown\MarkdownExtension;
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
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new MarkdownExtension());
        $this->twig->addRuntimeLoader(new class implements RuntimeLoaderInterface
        {
            public function load($class)
            {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }
            }
        });

        $this->request = new HTTPRequest();
    }
}
