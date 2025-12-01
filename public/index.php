<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// add routing middleware and error middleware
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Register DB in container
$container->set('db_path', __DIR__ . '/../data/database.sqlite');

// include routes
(require __DIR__ . '/../src/routes.php')($app);

$app->run();

