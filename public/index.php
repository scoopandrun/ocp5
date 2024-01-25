<?php

setlocale(LC_ALL, "fr_FR.utf8", "fr-FR");
date_default_timezone_set('Europe/Paris');

/**
 * This is the main controller or router.
 */

define("ROOT", dirname(__DIR__));
define("TEMPLATES", ROOT . "/templates");

require_once(ROOT . "/vendor/autoload.php");

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT, "/.env");
$dotenv->load();

use App\Core\Router;
use App\Core\ErrorLogger;
use App\Controller\HomepageController;
use App\Controller\PostController;
use App\Controller\AdminDashboardController;
use App\Controller\PostManagementController;
use App\Controller\ErrorController;
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
    "/admin" => [
        "GET" => fn () => (new AdminDashboardController())->show(),
    ],
    "/admin/posts" => [
        "GET" => fn () => (new PostManagementController())->show(),
    ],
    "/admin/posts/(\d+)" => [
        "GET" => fn (int $id) => (new PostManagementController())->showEditPage($id),
        "POST" => fn (int $id) => (new PostManagementController())->editPost($id),
        "DELETE" => fn (int $id) => (new PostManagementController())->deletePost($id),
    ],
    "/admin/posts/create" => [
        "GET" => fn () => (new PostManagementController())->showEditPage(),
        "POST" => fn () => (new PostManagementController())->createPost(),
    ],
];

try {
    $router = new Router($routes);
    $router->match();
} catch (ClientException $e) {
    (new ErrorController($e))->show();
} catch (ServerException $e) {
    (new ErrorLogger($e))->log();
    (new ErrorController($e))->show();
} catch (\Throwable $th) {
    (new ErrorLogger($th))->log();
    (new ErrorController(new ServerException()))->show();
}
