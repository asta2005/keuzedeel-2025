<?php

use App\Controllers\HomeController;

$app->get('/', [HomeController::class, 'index']);
$app->get('/hello/{name}', [HomeController::class, 'hello']);
