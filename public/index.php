<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// App maken
$app = AppFactory::create();


// Dependencies en routes
require __DIR__ . '/../app/dependencies.php';
require __DIR__ . '/../app/routes.php';

// Error middleware
$app->addErrorMiddleware(true, true, true);

$app->run();
