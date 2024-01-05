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

        $url = parse_url($_SERVER['REQUEST_URI']);
        $this->path = $url["path"];
        parse_str($url["query"] ?? "", $this->query);

        $this->body =
            !empty($_POST)
            ? $_POST
            : (array) json_decode(file_get_contents("php://input"), true);
    }
}
