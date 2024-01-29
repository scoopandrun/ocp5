<?php

namespace App\Controller;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\Markdown\MarkdownExtension;
use App\Core\HTTP\HTTPRequest;
use App\Core\HTTP\HTTPResponse;

abstract class Controller
{
    private FilesystemLoader $loader;
    protected Environment $twig;

    protected HTTPRequest $request;
    protected HTTPResponse $response;

    public function __construct()
    {
        $this->request = new HTTPRequest();
        $this->response = new HTTPResponse();

        $this->loader = new FilesystemLoader(TEMPLATES);
        $this->twig = new Environment($this->loader);
        $this->twig->addGlobal("user", $this->request->user);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new MarkdownExtension());
        $this->twig->addRuntimeLoader(
            new class implements RuntimeLoaderInterface
            {
                public function load($class)
                {
                    if (MarkdownRuntime::class === $class) {
                        return new MarkdownRuntime(new DefaultMarkdown());
                    }
                }
            }
        );
    }

    protected function sendResponseWithSingleMessage(
        string $template,
        string $messageTitle,
        string $message,
        int $statusCode = 200
    ): void {
        $this->response->setCode($statusCode);

        // HTML
        if ($this->request->acceptsHTML()) {
            $this->response
                ->sendHTML(
                    $this->twig->render(
                        $template,
                        [
                            "$messageTitle" => [
                                "message" => $message
                            ]
                        ]
                    )
                );
        }

        // JSON
        if ($this->request->acceptsJSON()) {
            $this->response
                ->sendJSON(
                    json_encode(["message" => $message])
                );
        }

        // Text (default)
        $this->response->sendText($message);
    }
}
