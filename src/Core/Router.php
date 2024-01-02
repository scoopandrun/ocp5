<?php

namespace App\Core;

use App\Core\Exceptions\Client\NotFoundException;

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
        }

        $routeMatched = false;

        foreach ($this->routes as $path => $controller) {
            if (preg_match("#^{$path}/?$#i", $uri, $matches)) {
                $routeMatched = true;
                array_shift($matches); // Preserve only captured matches
                $controller(...$matches);
                return;
            }
        }

        if ($routeMatched === false) {
            throw new NotFoundException;
        }
    }
}
