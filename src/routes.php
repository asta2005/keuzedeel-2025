<?php
use Slim\App;
use Slim\Views\Twig;
use App\Database;
use App\Controllers\ProjectController;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function(App $app) {

    $container = $app->getContainer();
    $dbPath = $container->get('db_path');

    $db = new Database($dbPath);
    $pdo = $db->getPdo();
    $twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

    // Instantiate controller
    $controller = new ProjectController($pdo, $twig, __DIR__ . '/../public/uploads');

    /**
     * ----------------------------------------------------
     * PUBLIC ROUTES
     * ----------------------------------------------------
     */

    $app->get('/', [$controller, 'list']);
    $app->get('/projects', [$controller, 'list']);
    $app->get('/project/{id}', [$controller, 'single']);


    /**
     * ----------------------------------------------------
     * LOGIN ROUTES
     * ----------------------------------------------------
     */

    $app->get('/admin/login', function(Request $req, Response $res) use ($twig) {
        return $twig->render($res, 'admin_login.twig');
    });

    $app->post('/admin/login', function(Request $req, Response $res) {
        $data = $req->getParsedBody();

        // Simple demo admin password - change in production
        if (!empty($data['password']) && $data['password'] === 'adminpass') {
            $_SESSION['is_admin'] = true;
            return $res->withHeader('Location', '/admin')->withStatus(302);
        }

        return $res->withHeader('Location', '/admin/login')->withStatus(302);
    });

    /**
     * ----------------------------------------------------
     * ADMIN MIDDLEWARE
     * ----------------------------------------------------
     */

    $adminMiddleware = function($request, $handler) {
        if (empty($_SESSION['is_admin'])) {
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', '/admin/login')->withStatus(302);
        }
        return $handler->handle($request);
    };


    /**
     * ----------------------------------------------------
     * ADMIN AREA (Protected)
     * ----------------------------------------------------
     */

    $app->group('/admin', function (RouteCollectorProxy $group) use ($controller) {

        $group->get('', [$controller, 'adminList']);
        $group->get('/add', [$controller, 'showAdd']);
        $group->post('/add', [$controller, 'handleAdd']);

    })->add($adminMiddleware);


    /**
     * ----------------------------------------------------
     * LOGOUT
     * ----------------------------------------------------
     */
    $app->get('/admin/logout', function(Request $req, Response $res) {
        unset($_SESSION['is_admin']);
        return $res->withHeader('Location', '/')->withStatus(302);
    });
};
