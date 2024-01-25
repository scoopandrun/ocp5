<?php

namespace App\Controller;

use App\Core\Exceptions\AppException;

class ErrorController extends Controller
{
    public function __construct(protected AppException $e)
    {
        parent::__construct();
    }

    public function show(): void
    {
        $this->response->setCode($this->e->httpStatus);

        // HTML response
        if ($this->request->acceptsHTML()) {
            $this->response
                ->setHTML($this->twig->render("front/error.html.twig", [
                    "error" => $this->e
                ]))
                ->send();
        }

        // JSON response
        if ($this->request->acceptsJSON()) {
            $json = json_encode([
                "message" => $this->e->getMessage(),
            ]);

            $this->response
                ->setJSON($json)
                ->send();
        }

        // Default response
        $this->response->setText($this->e->getMessage())->send();
    }
}
