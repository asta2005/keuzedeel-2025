<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeController
{
    public function index(Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig', [
            'title' => 'Welkom bij Slim Framework!',
            'message' => 'Dit is een voorbeeldpagina met Twig.'
        ]);
    }

    public function hello(Request $request, Response $response, array $args)
    {
        $name = $args['name'];

        $response->getBody()->write("Hallo, $name!");
        return $response;
    }
}
