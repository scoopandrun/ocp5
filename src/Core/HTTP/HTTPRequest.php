<?php

namespace App\Core\HTTP;

use App\Entity\User;
use App\Service\UserService;

/**
 * HTTP request simple wrapper to give
 * easy access to common request properties.
 */
class HTTPRequest
{
    /**
     * Request HTTP method.
     */
    public readonly string $method;

    /**
     * Request headers.
     */
    protected array $headers = [];

    /**
     * URL path.
     */
    public readonly string $path;

    /**
     * URL query string (after "?").
     */
    public array $query = [];

    /**
     * Request body.
     */
    public readonly array $body;

    /**
     * User performing the request.
     */
    public readonly ?User $user;

    public function __construct(bool $emergency = false)
    {
        $this->method = $_SERVER["REQUEST_METHOD"];

        $this->headers = getallheaders();

        if (isset($this->headers["Accept"])) {
            $this->headers["Accept"] = explode(",", $this->headers["Accept"]);
        }

        $url = parse_url($_SERVER['REQUEST_URI']);
        $this->path = $url["path"];
        parse_str($url["query"] ?? "", $this->query);

        $this->body =
            !empty($_POST)
            ? $_POST
            : (array) json_decode(file_get_contents("php://input"), true);


        if (isset($_SESSION["userId"]) && !$emergency) {
            $this->user = (new UserService())->getUser($_SESSION["userId"]);
        } else {
            $this->user = null;
        }
    }

    /**
     * Returns `true` if a request accepts an HTML response, `false` otherwise.
     * @return bool 
     */
    public function acceptsHTML()
    {
        return in_array("text/html", $this->headers["Accept"] ?? []);
    }

    /**
     * Returns `true` if a request accepts an HTML response, `false` otherwise.
     * @return bool 
     */
    public function acceptsJSON()
    {
        return in_array("application/json", $this->headers["Accept"] ?? []);
    }
}
