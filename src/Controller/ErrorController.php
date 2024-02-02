<?php

namespace App\Controller;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\Client\ClientException;
use App\Core\Exceptions\Server\ServerException;
use App\Core\ErrorLogger;

class ErrorController extends Controller
{
    public function __construct(protected AppException $e, bool $emergency = false)
    {
        parent::__construct($emergency);

        if (!$e instanceof ClientException) {
            (new ErrorLogger($e))->log();
        }
    }

    public function show(): void
    {
        $this->response->setCode($this->e->httpStatus);

        // HTML response
        if ($this->request->acceptsHTML()) {
            $this->response
                ->sendHTML($this->twig->render("front/error.html.twig", [
                    "error" => $this->e
                ]));
            return;
        }

        // JSON response
        if ($this->request->acceptsJSON()) {
            $json = json_encode([
                "message" => $this->e->getMessage(),
            ]);

            $this->response->sendJSON($json);
            return;
        }

        // Default response
        $this->response->sendText($this->e->getMessage());
    }

    static public function emergencyShow(\Throwable $e): void
    {
        $serverException = new ServerException(previous: $e);
        (new static($serverException, true))->show();
    }
}
