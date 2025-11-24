<?php

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$twig = Twig::create(__DIR__ . '/../templates', [
    'cache' => false
]);

// Twig middleware aan Slim app toevoegen
$app->add(TwigMiddleware::create($app, $twig));
