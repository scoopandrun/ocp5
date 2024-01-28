<?php

/**
 * This is the main controller or router.
 */

setlocale(LC_ALL, "fr_FR.utf8", "fr-FR");
date_default_timezone_set('Europe/Paris');

define("ROOT", dirname(__DIR__));
define("TEMPLATES", ROOT . "/templates");

require_once(ROOT . "/vendor/autoload.php");

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(ROOT, "/.env");
$dotenv->load();

// Session
session_start([
    "name" => $_ENV["SESSION_COOKIE_NAME"],
    "cookie_lifetime" => $_ENV["SESSION_EXPIRATION"] ?? 3600,
    "cookie_path" => $_ENV["SESSION_COOKIE_PATH"] ?? "/",
    "cookie_httponly" => true,
]);

use App\Core\Router;
use App\Core\ErrorLogger;
use App\Controller\HomepageController;
use App\Controller\PostController;
use App\Controller\UserController;
use App\Controller\Admin\DashboardController;
use App\Controller\Admin\PostManagementController;
use App\Controller\Admin\UserManagementController;
use App\Controller\ErrorController;
use App\Core\Exceptions\Client\ClientException;
use App\Core\Exceptions\Server\ServerException;

$routes = [
    // Front office
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
    "/user" => [
        "GET" => fn () => (new UserController())->showAccountPage(),
        "POST" => fn () => (new UserController())->editAccount(),
        "DELETE" => fn () => (new UserController())->deleteAccount(),
    ],
    "/user/delete" => [
        "GET" => fn () => (new UserController())->showDeleteAccountConfirmation(),
        "POST" => fn () => (new UserController())->deleteAccount(),
    ],
    "/user/sendVerificationEmail" => [
        "GET" => fn () => (new UserController())->sendVerificationEmail(),
    ],
    "/user/verifyEmail/([\w-]{21})" => [
        "GET" => fn (string $token) => (new UserController())->verifyEmail($token),
    ],
    "/login" => [
        "GET" => fn () => (new UserController())->showLoginPage(),
        "POST" => fn () => (new UserController())->login(),
    ],
    "/logout" => [
        "GET" => fn () => (new UserController())->logout(),
    ],
    "/signup" => [
        "GET" => fn () => (new UserController())->showSignupPage(),
        "POST" => fn () => (new UserController())->createAccount(),
    ],
    // Back office
    "/admin" => [
        "GET" => fn () => (new DashboardController())->show(),
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
    "/admin/users" => [
        "GET" => fn () => (new UserManagementController())->show(),
    ],
    "/admin/users/(\d+)" => [
        "GET" => fn (int $id) => (new UserManagementController())->showEditPage($id),
        "POST" => fn (int $id) => (new UserManagementController())->editUser($id),
        "DELETE" => fn (int $id) => (new UserManagementController())->deleteUser($id),
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
