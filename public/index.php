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

use App\Core\{
    Constants,
    Router,
    HTTP\HTTPResponse,
};
use App\Core\Exceptions\{
    AppException,
    Server\ServerException,
};
use App\Controller\{
    ErrorController,
    HomepageController,
    PostController,
    CommentController,
    UserController,
    Admin\DashboardController,
    Admin\PostManagementController,
    Admin\CommentManagementController,
    Admin\CategoryManagementController,
    Admin\UserManagementController,
};

Constants::setRoot(ROOT);
Constants::setTemplates(TEMPLATES);

set_exception_handler(["\App\Controller\ErrorController", "emergencyShow"]);

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
    "/posts/(\d+)/comments" => [
        "GET" => fn (int $postId) => (new CommentController())->redirectToPostPage($postId),
        "POST" => fn (int $postId) => (new CommentController())->createComment($postId),
    ],
    "/posts/(\d+)/comments/(\d+)" => [
        "GET" => fn (int $postId) => (new CommentController())->redirectToPostPage($postId),
        "DELETE" => fn (int $postId, int $commentId) => (new CommentController())->deleteComment($commentId),
    ],
    "/posts/(\d+)/comments/(\d+)/delete" => [
        "GET" => fn (int $postId) => (new CommentController())->redirectToPostPage($postId),
        "POST" => fn (int $postId, int $commentId) => (new CommentController())->deleteComment($commentId),
    ],
    "/posts/(\d+)/comments/create" => [
        "GET" => fn (int $postId) => (new CommentController())->redirectToPostPage($postId),
        "POST" => fn (int $postId) => (new CommentController())->createComment($postId),
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
    "/passwordReset" => [
        "GET" => fn () => (new UserController())->showPaswordResetAskEmailPage(),
        "POST" => fn () => (new UserController())->sendPasswordResetEmail(),
    ],
    "/passwordReset/([\w-]{21})" => [
        "GET" => fn (string $token) => (new UserController())->showPaswordResetChangePasswordPage($token),
        "POST" => fn (string $token) => (new UserController())->resetPassword($token),
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
    "/admin/categories" => [
        "GET" => fn () => (new CategoryManagementController())->show(),
    ],
    "/admin/categories/(\d+)" => [
        "GET" => fn (int $id) => (new CategoryManagementController())->showEditPage($id),
        "POST" => fn (int $id) => (new CategoryManagementController())->editCategory($id),
    ],
    "/admin/categories/create" => [
        "GET" => fn () => (new CategoryManagementController())->showEditPage(),
        "POST" => fn () => (new CategoryManagementController())->createCategory(),
    ],
    "/admin/comments" => [
        "GET" => fn () => (new CommentManagementController())->show(),
    ],
    "/admin/comments/(\d+)" => [
        "GET" => fn (int $id) => (new CommentManagementController())->showReviewPage($id),
    ],
    "/admin/comments/(\d+)/approve" => [
        "GET" => fn (int $id) => (new CommentManagementController())->showReviewPage($id),
        "POST" => fn (int $id) => (new CommentManagementController())->approveComment($id),
    ],
    "/admin/comments/(\d+)/reject" => [
        "GET" => fn (int $id) => (new CommentManagementController())->showReviewPage($id),
        "POST" => fn (int $id) => (new CommentManagementController())->rejectComment($id),
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

$response = new HTTPResponse();

try {
    $router = new Router($routes);
    $response = $router->match();
} catch (AppException $e) {
    $response = (new ErrorController($e))->show();
} catch (\Throwable $th) {
    $response = (new ErrorController(new ServerException(previous: $th)))->show();
}

$response->send();
