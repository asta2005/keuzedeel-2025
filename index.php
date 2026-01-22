<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Frontend
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\ProjectController;
use App\Controllers\PublicationController;
use App\Controllers\ContactFormController;
use App\Controllers\ApplicationController;

// Auth
use App\Controllers\Auth\UserAuthController;
use App\Controllers\Auth\RegisterController;

// Admin
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\Admin\AdminProjectController;
use App\Controllers\Admin\AdminContactController;
use App\Controllers\Admin\AdminApplicationController;
use App\Controllers\Admin\AdminVacancyController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$app = AppFactory::create();

/* ============================
   ADMIN GUARD
============================ */
function requireAdmin(Response $response): ?Response {
    if (($_SESSION['role'] ?? null) !== 'admin') {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    return null;
}

/* ============================
   SHARED LAYOUT + SEO
============================ */
function renderLayout(string $title, string $content, string $description = ''): string
{
    $username = $_SESSION['user'] ?? null;
    $role = $_SESSION['role'] ?? null;

    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $metaDescription = $description
        ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8')
        : 'PMB Amsterdam – Projectmanagement, expertise en publieke projecten voor de Gemeente Amsterdam.';

    $host = $_SERVER['HTTP_HOST'] ?? 'pmbamsterdam.nl';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $canonical = 'https://' . $host . $uri;

    // Footer auth
    if ($username) {
        $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $adminLink = ($role === 'admin')
            ? '<a class="footer-auth__link" href="/admin">Admin panel</a>'
            : '';

        $footerAuth = <<<HTML
<div class="footer-auth" aria-label="Account">
  <div class="footer-auth__name">{$safeUser}</div>
  <div class="footer-auth__links">
    {$adminLink}
    <a class="footer-auth__link footer-auth__link--logout" href="/logout">Uitloggen</a>
  </div>
</div>
HTML;
    } else {
        $footerAuth = '<a class="footer-auth__link footer-auth__link--login" href="/login">Inloggen</a>';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>{$safeTitle} | PMB Amsterdam</title>
<meta name="description" content="{$metaDescription}">
<link rel="canonical" href="{$canonical}">
<meta name="robots" content="index, follow">

<link rel="preload" href="/css/style.css" as="style">
<link rel="stylesheet" href="/css/style.css">

<meta property="og:type" content="website">
<meta property="og:title" content="{$safeTitle} | PMB Amsterdam">
<meta property="og:description" content="{$metaDescription}">
<meta property="og:url" content="{$canonical}">
<meta property="og:image" content="https://{$host}/img/logo.webp">
</head>

<body>

<header class="topbar">
  <div class="header-container">
    <div class="logo">
      <a href="/" aria-label="PMB Amsterdam home">
        <img 
          src="/img/logo.webp"
          alt="PMB Amsterdam – Gemeente Amsterdam"
          width="80"
          height="80"
          decoding="async"
        >
      </a>
    </div>

    <nav class="main-nav" aria-label="Hoofdnavigatie">
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/werken-bij">Werken bij</a></li>
        <li><a href="/opdrachten-en-projecten">Projecten</a></li>
        <li><a href="/expertise">Expertise</a></li>
        <li><a href="/projectmanagement">Projectmanagement</a></li>
        <li><a href="/publicaties">Publicaties</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </nav>
  </div>
</header>

<main id="content">
{$content}
</main>

<footer class="site-footer">
  <div class="site-footer__inner">
    <div class="site-footer__left">
      <span class="site-footer__copy">&copy; PMB Amsterdam – Gemeente Amsterdam</span>
    </div>
    <div class="site-footer__right">
      {$footerAuth}
    </div>
  </div>
</footer>

</body>
</html>
HTML;
}

/* ============================
   FRONTEND ROUTES
============================ */
$app->get('/', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Home', (new HomeController())->index(), 'Welkom bij PMB Amsterdam.'));
    return $s;
});

$app->get('/werken-bij', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Werken bij', (new ApplicationController())->page($r), 'Vacatures en solliciteren bij PMB Amsterdam.'));
    return $s;
});

$app->post('/solliciteer', function (Request $r, Response $s) {
    return (new ApplicationController())->submit($r, $s);
});

$app->get('/expertise', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Expertise', (new PageController())->showBySlug('expertise')));
    return $s;
});

$app->get('/projectmanagement', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Projectmanagement', (new PageController())->showBySlug('projectmanagement')));
    return $s;
});

$app->get('/opdrachten-en-projecten', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Projecten', (new ProjectController())->index(), 'Bekijk opdrachten en projecten van PMB Amsterdam.'));
    return $s;
});

$app->get('/publicaties', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Publicaties', (new PublicationController())->index()));
    return $s;
});

$app->get('/contact', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Contact', (new ContactFormController())->show()));
    return $s;
});

$app->post('/contact', function (Request $r, Response $s) {
    $inner = (new ContactFormController())->submit($r);
    $s->getBody()->write(renderLayout('Contact', $inner));
    return $s;
});

/* ============================
   AUTH
============================ */
$app->get('/login', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Inloggen', (new UserAuthController())->loginForm()));
    return $s;
});

$app->post('/login', function (Request $r, Response $s) {
    return (new UserAuthController())->login($r, $s);
});

$app->get('/logout', function (Request $r, Response $s) {
    return (new UserAuthController())->logout($r, $s);
});

$app->get('/register', function (Request $r, Response $s) {
    $s->getBody()->write(renderLayout('Registreren', (new RegisterController())->registerForm()));
    return $s;
});

$app->post('/register', function (Request $r, Response $s) {
    return (new RegisterController())->register($r, $s);
});

/* ============================
   ADMIN ROUTES
============================ */
$app->get('/admin', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Admin', (new AdminDashboardController())->index()));
    return $s;
});

$app->get('/admin/users', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Gebruikers', (new AdminUserController())->index()));
    return $s;
});

$app->get('/admin/projects', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Projectbeheer', (new AdminProjectController())->index()));
    return $s;
});

/* ✅ FIX: Project CRUD routes (edit/delete waren stuk) */
$app->post('/admin/projects/add', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    return (new AdminProjectController())->add($r, $s);
});

$app->get('/admin/projects/edit/{id}', function (Request $r, Response $s, array $a) {
    if ($resp = requireAdmin($s)) return $resp;
    $id = (int)($a['id'] ?? 0);
    $s->getBody()->write(renderLayout('Project bewerken', (new AdminProjectController())->editPage($id)));
    return $s;
});

$app->post('/admin/projects/update/{id}', function (Request $r, Response $s, array $a) {
    if ($resp = requireAdmin($s)) return $resp;
    return (new AdminProjectController())->update($r, $s, $a);
});

$app->get('/admin/projects/delete/{id}', function (Request $r, Response $s, array $a) {
    if ($resp = requireAdmin($s)) return $resp;
    return (new AdminProjectController())->delete($r, $s, $a);
});

$app->get('/admin/inbox', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Inbox', (new AdminContactController())->index()));
    return $s;
});

/* Sollicitaties */
$app->get('/admin/sollicitaties', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Sollicitaties', (new AdminApplicationController())->index()));
    return $s;
});

/* ✅ LET OP: Deze route is nodig voor CV download */
$app->get('/admin/sollicitaties/cv/{id}', [new AdminApplicationController(), 'downloadCv']);

/* Vacatures */
$app->get('/admin/vacatures', function (Request $r, Response $s) {
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Vacatures', (new AdminVacancyController())->index()));
    return $s;
});

$app->post('/admin/vacatures/store', [new AdminVacancyController(), 'store']);
$app->get('/admin/vacatures/edit/{id}', [new AdminVacancyController(), 'edit']);
$app->post('/admin/vacatures/update/{id}', [new AdminVacancyController(), 'update']);
$app->get('/admin/vacatures/delete/{id}', [new AdminVacancyController(), 'delete']);

$app->run();
