<?php

//composer init // todo enter
//composer install
//composer dump-autoload -o 
//composer require slim/slim:"4.*"
//composer require slim/psr7
//composer require illuminate/database
//composer require "illuminate/events"
//composer require firebase/php-jwt

use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use Config\Database;
use App\Controllers\UsuarioController;
use App\Controllers\LoginController;
use App\Controllers\MateriaController;
use App\Middlewares\JsonMiddleware;
use App\Middlewares\AuthMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$conn = new Database;

$app = AppFactory::create();

$app->setBasePath('/PROG3-PARCIAL2/public');


$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('[/]', UsuarioController::class.":getAll");
    $group->post('[/]', UsuarioController::class.":addOne");
    $group->post('/{legajo}', UsuarioController::class.":updateOne")->add(new AuthMiddleware(1,2,3));
})
->add(new JsonMiddleware);

$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', LoginController::class.":login");
})
->add(new JsonMiddleware);

$app->group('/materias', function (RouteCollectorProxy $group) {
    $group->get('[/]', MateriaController::class.":getAll")->add(new AuthMiddleware(1,2,3));
    $group->post('[/]', MateriaController::class.":addOne")->add(new AuthMiddleware(1))->add(new JsonMiddleware);
})
->add(new JsonMiddleware);

$app->addBodyParsingMiddleware(); // para que funcione el put
$app->run();
