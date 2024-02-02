<?php

namespace App\Controller;

use App\Service\TwigService;
use Twig\Environment;
use App\Core\HTTP\HTTPRequest;
use App\Core\HTTP\HTTPResponse;

abstract class Controller
{
    protected Environment $twig;

    protected HTTPRequest $request;
    protected HTTPResponse $response;

    /**
     * @param bool $emergency Avoid all risky calls (eg: database) in case of a serious crash.
     */
    public function __construct(bool $emergency = false)
    {
        $this->request = new HTTPRequest($emergency);
        $this->response = new HTTPResponse();

        $this->twig = (new TwigService($this->request))->getEnvironment();
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
            return;
        }

        // JSON
        if ($this->request->acceptsJSON()) {
            $this->response
                ->sendJSON(
                    json_encode(["message" => $message])
                );
            return;
        }

        // Text (default)
        $this->response->sendText($message);
    }
}
