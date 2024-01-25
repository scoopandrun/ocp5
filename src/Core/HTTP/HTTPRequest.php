<?php

namespace App\Core\HTTP;

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
    public array $body = [];

    public function __construct()
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
