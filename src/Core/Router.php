<?php

namespace App\Core;

use App\Core\Exceptions\Client\NotFoundException;

/**
 * Homemade basic router.
 */
class Router
{
    public function __construct(public array $routes)
    {
    }

    /**
     * Match a route and calls the associated controller.
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
                $controller(...$matches)->processRequest();
                return;
            }
        }

        if ($routeMatched === false) {
            throw new NotFoundException;
        }
    }
}
