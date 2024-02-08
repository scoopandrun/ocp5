<?php

namespace App\Controller;

use App\Core\HTTP\HTTPResponse;
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

    public function show(): HTTPResponse
    {
        $this->response->setCode($this->e->httpStatus);

        // JSON response
        if ($this->request->acceptsJSON()) {
            $json = json_encode([
                "message" => $this->e->getMessage(),
            ]);

            return $this->response->setJSON($json);
        }

        return $this->response
            ->setHTML($this->twig->render("front/error.html.twig", [
                "error" => $this->e
            ]));
    }

    static public function emergencyShow(\Throwable $e): void
    {
        $serverException = new ServerException(previous: $e);
        $response = (new static($serverException, true))->show();
        $response->send();
    }
}
