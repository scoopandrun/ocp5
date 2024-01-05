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
use App\Controllers\Homepage;
use App\Controllers\Error;
use App\Core\Exceptions\Client\ClientException;
use App\Core\Exceptions\Server\ServerException;

$routes = [
    "/" => fn () => (new Homepage())->show(),
];

try {
    $router = new Router($routes);
    $router->match();
} catch (ClientException $e) {
    (new Error($e))->show();
} catch (ServerException $e) {
    error_logger($e);
    (new Error($e))->show();
} catch (\Throwable $th) {
    error_logger($th);
    (new Error(new ServerException))->show();
}
