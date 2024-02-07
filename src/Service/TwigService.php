<?php

namespace App\Service;

use App\Core\Constants;
use App\Core\Security;
use App\Core\HTTP\HTTPRequest;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\Markdown\MarkdownExtension;
use nadar\quill\Lexer;
use Twig\TwigFilter;

class TwigService
{
    private FilesystemLoader $loader;
    private Environment $environment;

    public function __construct(?HTTPRequest $request = null)
    {
        $this->loader = new FilesystemLoader(Constants::$TEMPLATES);

        $this->environment = new Environment($this->loader);

        if ($request) {
            $this->environment->addGlobal("user", $request->user);
        }

        $this->environment->addGlobal("MINIMUM_PASSWORD_LENGTH", Security::MINIMUM_PASSWORD_LENGTH);

        $this->environment->addExtension(new IntlExtension());
        $this->environment->addExtension(new MarkdownExtension());
        $this->environment->addRuntimeLoader(
            new class implements RuntimeLoaderInterface
            {
                public function load(string $class)
                {
                    if (MarkdownRuntime::class === $class) {
                        return new MarkdownRuntime(new DefaultMarkdown());
                    }
                }
            }
        );

        $this->environment->addFilter(
            new TwigFilter(
                "quill",
                fn (string $delta) => (new Lexer($delta))->render()
            )
        );
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}
