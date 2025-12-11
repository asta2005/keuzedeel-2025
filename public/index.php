<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

session_start();

/* ============================================================
   CONTROLLERS LADEN
============================================================ */
use App\Controllers\HomeController;
use App\Controllers\ProjectController;
use App\Controllers\PublicationController;
use App\Controllers\PageController;

// AUTH
use App\Controllers\Auth\UserAuthController;
use App\Controllers\Auth\RegisterController;

// ADMIN
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminUserController;

$app = AppFactory::create();

/* ============================================================
   LAYOUT WRAPPER
============================================================ */
function renderLayout(string $title, string $content): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>{$title} - PMB Amsterdam</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="topbar">
    <nav class="main-nav">
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/werken-bij">Werken bij</a></li>
            <li><a href="/expertise">Expertise</a></li>
            <li><a href="/opdrachten-en-projecten">Projecten</a></li>
            <li><a href="/publicaties">Publicaties</a></li>
            <li><a href="/contact">Contact</a></li>
            <li><a href="/login">Login</a></li>
            <li><a href="/admin">Admin</a></li>
        </ul>
    </nav>
</header>

<main>
{$content}
</main>

<footer>© PMB Amsterdam - Gemeente Amsterdam</footer>
</body>
</html>
HTML;
}

/* ============================================================
   FRONTEND ROUTES
============================================================ */

// Home
$app->get('/', function ($req, $res) {
    $controller = new HomeController();
    $html = renderLayout("Home", $controller->index());
    $res->getBody()->write($html);
    return $res;
});

// Projecten (voorbeeld)
$app->get('/opdrachten-en-projecten', function ($req, $res) {
    $controller = new ProjectController();
    $html = renderLayout("Projecten", $controller->index());
    $res->getBody()->write($html);
    return $res;
});

// Publicaties
$app->get('/publicaties', function ($req, $res) {
    $controller = new PublicationController();
    $html = renderLayout("Publicaties", $controller->index());
    $res->getBody()->write($html);
    return $res;
});

// Dynamische paginamodellen
$app->get('/werken-bij', function ($req, $res) {
    $controller = new PageController();
    $html = renderLayout("Werken bij", $controller->showBySlug('werken-bij'));
    $res->getBody()->write($html);
    return $res;
});

$app->get('/expertise', function ($req, $res) {
    $controller = new PageController();
    $html = renderLayout("Expertise", $controller->showBySlug('expertise'));
    $res->getBody()->write($html);
    return $res;
});

$app->get('/contact', function ($req, $res) {
    $controller = new PageController();
    $html = renderLayout("Contact", $controller->showBySlug('contact'));
    $res->getBody()->write($html);
    return $res;
});

/* ============================================================
   AUTH ROUTES (LOGIN / REGISTER)
============================================================ */

// LOGIN FORM
$app->get('/login', function ($req, $res) {
    $html = (new UserAuthController())->loginForm();
    $res->getBody()->write(renderLayout("Inloggen", $html));
    return $res;
});

// LOGIN PROCESS
$app->post('/login', [new UserAuthController(), 'login']);

// LOGOUT
$app->get('/logout', [new UserAuthController(), 'logout']);

// REGISTER FORM
$app->get('/register', function ($req, $res) {
    $html = (new RegisterController())->registerForm();
    $res->getBody()->write(renderLayout("Registreren", $html));
    return $res;
});

// REGISTER PROCESS
$app->post('/register', [new RegisterController(), 'register']);

/* ============================================================
   ADMIN PANEL (ALLEEN ROLE = 'admin')
============================================================ */

// ADMIN DASHBOARD
$app->get('/admin', function ($req, $res) {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        return $res->withHeader("Location", "/login?error=Geen+admin+rechten")
                   ->withStatus(302);
    }

    $controller = new AdminDashboardController();
    $html = renderLayout("Admin Paneel", $controller->index());
    $res->getBody()->write($html);
    return $res;
});

// ADMIN: gebruikerslijst
$app->get('/admin/users', function ($req, $res) {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        return $res->withHeader("Location", "/login?error=Geen+toegang")
                   ->withStatus(302);
    }

    $controller = new AdminUserController();
    $html = renderLayout("Gebruikersbeheer", $controller->list());
    $res->getBody()->write($html);
    return $res;
});

// ADMIN: promote user
$app->get('/admin/user/make-admin/{id}', function ($req, $res, $args) {
    (new AdminUserController())->promote($args['id']);
    return $res;
});

// ADMIN: demote user
$app->get('/admin/user/make-user/{id}', function ($req, $res, $args) {
    (new AdminUserController())->demote($args['id']);
    return $res;
});

/* ============================================================
   GENERIC PAGE BY SLUG
============================================================ */
$app->get('/page/{slug}', function ($req, $res, $args) {
    $controller = new PageController();
    $html = renderLayout($args['slug'], $controller->showBySlug($args['slug']));
    $res->getBody()->write($html);
    return $res;
});

/* ============================================================
   APP START
============================================================ */

$app->run();
