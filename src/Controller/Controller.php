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

    protected function setResponseWithSingleMessage(
        string $template,
        string $messageTitle,
        string $message,
        int $statusCode = 200
    ): HTTPResponse {
        $this->response->setCode($statusCode);

        // JSON
        if ($this->request->acceptsJSON()) {
            return $this->response->setJSON(
                json_encode(["message" => $message])
            );
        }

        return $this->response
            ->setHTML(
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
}
