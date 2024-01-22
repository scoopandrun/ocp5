<?php

setlocale(LC_ALL, "fr_FR.utf8", "fr-FR");
date_default_timezone_set('Europe/Paris');

/**
 * This is the main controller or router.
 */

define("ROOT", dirname(__DIR__));
define("TEMPLATES", ROOT . "/templates");

require_once(ROOT . "/vendor/autoload.php");
require_once(ROOT . "/utils/utils.php");

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT, "/.env");
$dotenv->load();

use App\Core\Router;
use App\Controllers\HomepageController;
use App\Controllers\PostController;
use App\Controllers\ErrorController;
use App\Core\Exceptions\Client\ClientException;
use App\Core\Exceptions\Server\ServerException;

$routes = [
    "/" => [
        "GET" => fn () => (new HomepageController())->show(),
        "POST" => fn () => (new HomepageController())->processContactForm(),
    ],
    "/posts" => [
        "GET" => fn () => (new PostController())->showAll(),
    ],
    "/posts/(\d+)" => [
        "GET" => fn (int $id) => (new PostController())->showOne($id),
    ],
    "/posts/(\d+)/edit" => [
        "GET" => fn (int $id) => (new PostController())->showEditPage($id),
        "POST" => fn (int $id) => (new PostController())->editPost($id),
    ],
    "/posts/create" => [
        "GET" => fn () => (new PostController())->showEditPage(),
        "POST" => fn () => (new PostController())->createPost(),
    ],
];

try {
    $router = new Router($routes);
    $router->match();
} catch (ClientException $e) {
    (new ErrorController($e))->show();
} catch (ServerException $e) {
    error_logger($e);
    (new ErrorController($e))->show();
} catch (\Throwable $th) {
    error_logger($th);
    (new ErrorController(new ServerException()))->show();
}
