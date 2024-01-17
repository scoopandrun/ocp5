<?php

namespace App\Core;

use App\Core\Exceptions\Client\{NotFoundException, MethodNotAllowedException};

/**
 * Homemade basic router.
 */
class Router
{
    /**
     * @param array $routes An array of routes.  
     *                      Routes must be of the form `["path_regex" => fn (...) => new Controller(...)]`
     */
    public function __construct(public array $routes)
    {
    }

    /**
     * Match a route and call the associated controller.
     * 
     * @param string $uri Optional. Path to be matched.
     */
    public function match(string $uri = "")
    {
        if (!$uri) {
            $uri = $_SERVER["REQUEST_URI"] ?? "/";
            $uri = preg_replace("/\?.*/", "", $uri);
        }

        $requestMethod = $_SERVER["REQUEST_METHOD"] ?? "";

        $routeMatched = false;
        $methodMatched = false;

        foreach ($this->routes as $path => $handlers) {
            if (preg_match("#^{$path}/?$#i", $uri, $matches)) {
                foreach ($handlers as $method => $controller) {
                    if (strtolower($method) === strtolower($requestMethod)) {
                        $routeMatched = $methodMatched = true;
                        array_shift($matches); // Preserve only captured matches
                        $controller(...$matches);
                        return;
                    }
                }

                if ($methodMatched === false) {
                    throw new MethodNotAllowedException();
                }
            }
        }

        if ($routeMatched === false) {
            throw new NotFoundException();
        }
    }
}
