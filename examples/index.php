<?php

declare(strict_types=1);

use Source\Router;

ob_start();

require dirname(__DIR__, 1) . "/vendor/autoload.php";

require __DIR__ . "/App/Controllers/Web.php";
require __DIR__ . "/App/Middlewares/Middlewares.php";
require __DIR__ . "/App/Middlewares/Guest.php";

use App\Middlewares\Middlewares as Middleware;

const ROOT = "http://localhost:8040/router/examples";

$router = new Router(ROOT);
$router->namespace("App\Controllers");

$router->get("/", "Web:home");
$router->get("/cadastrar", "Web:register", "web.register", Middleware::GUEST);

//Named Router with data on route
$router->post("/register/{id}", "Web:register", "web.register.post");

/**
 * Error
 */
$router->group("/ops");
$router->get("/{errcode}", "Web:error", "web.error");

$router->dispatch();

if ($router->error) {
    $router->redirect("web.error", ["errcode" => $router->error]);
}


ob_end_flush();