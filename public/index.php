<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Frontend
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\ProjectController;
use App\Controllers\PublicationController;
use App\Controllers\ContactFormController;

// Auth
use App\Controllers\Auth\UserAuthController;
use App\Controllers\Auth\RegisterController;

// Admin
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\Admin\AdminProjectController;
use App\Controllers\Admin\AdminContactController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$app = AppFactory::create();

/* ============================
   FLASH MESSAGE (NIET GEBRUIKT)
============================ */
function flash(): string {
    if (!empty($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='flash-success'>{$msg}</div>";
    }
    return '';
}

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
   SHARED LAYOUT
============================ */
function renderLayout(string $title, string $content): string
{
    $username = $_SESSION['user'] ?? null;
    $role = $_SESSION['role'] ?? null;

    if ($username) {
        $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $adminLink = ($role === 'admin')
            ? '<a class="user-dd-item" href="/admin">Admin panel</a>'
            : '';

        $authBlock = <<<HTML
<div class="user-menu">
    <button class="user-chip" type="button">
        <span class="user-chip__dot"></span>
        <span class="user-chip__name">{$safeUser}</span>
        <span class="user-chip__chev">▾</span>
    </button>
    <div class="user-dd">
        {$adminLink}
        <a class="user-dd-item" href="/logout">Uitloggen</a>
    </div>
</div>
HTML;
    } else {
        $authBlock = '<a class="login-link" href="/login">Inloggen</a>';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title} - PMB Amsterdam</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="topbar">
<div class="header-container">
    <div class="logo">
        <a href="/"><img src="/img/logo.png" alt="PMB Amsterdam"></a>
    </div>

    <nav class="main-nav">
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

    <div class="header-actions">
        {$authBlock}
    </div>
</div>
</header>

<main>
{$content}
</main>

<footer>
&copy; PMB Amsterdam – Gemeente Amsterdam
</footer>

<script>
(() => {
  const btn = document.querySelector('.user-chip');
  const dd = document.querySelector('.user-dd');
  if (!btn || !dd) return;
  btn.addEventListener('click', e => {
    e.preventDefault();
    dd.classList.toggle('open');
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('.user-menu')) dd.classList.remove('open');
  });
})();
</script>

</body>
</html>
HTML;
}

/* ============================
   FRONTEND ROUTES
============================ */

$app->get('/', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Home',(new HomeController())->index()));
    return $s;
});

$app->get('/werken-bij', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Werken bij',(new PageController())->showBySlug('werken-bij')));
    return $s;
});

$app->get('/expertise', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Expertise',(new PageController())->showBySlug('expertise')));
    return $s;
});

$app->get('/projectmanagement', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Projectmanagement',(new PageController())->showBySlug('projectmanagement')));
    return $s;
});

$app->get('/opdrachten-en-projecten', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Projecten',(new ProjectController())->index()));
    return $s;
});

$app->get('/publicaties', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Publicaties',(new PublicationController())->index()));
    return $s;
});

$app->get('/contact', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Contact',(new ContactFormController())->show()));
    return $s;
});

$app->post('/contact', function(Request $r, Response $s){
    $inner = (new ContactFormController())->submit($r);
    $s->getBody()->write(renderLayout('Contact',$inner));
    return $s;
});

/* ============================
   AUTH
============================ */

$app->get('/login', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Inloggen',(new UserAuthController())->loginForm()));
    return $s;
});

$app->post('/login',[new UserAuthController(),'login']);
$app->get('/logout',[new UserAuthController(),'logout']);

$app->get('/register', function(Request $r, Response $s){
    $s->getBody()->write(renderLayout('Registreren',(new RegisterController())->registerForm()));
    return $s;
});

$app->post('/register',[new RegisterController(),'register']);

/* ============================
   ADMIN (ADMINS ONLY)
============================ */

$app->get('/admin', function(Request $r, Response $s){
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Admin',(new AdminDashboardController())->index()));
    return $s;
});

$app->get('/admin/users', function(Request $r, Response $s){
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Gebruikers',(new AdminUserController())->index()));
    return $s;
});

$app->get('/admin/user/admin/{id}', fn($r,$s,$a)=>
    (new AdminUserController())->makeAdmin((int)$a['id']) ?: $s
);

$app->get('/admin/user/user/{id}', fn($r,$s,$a)=>
    (new AdminUserController())->makeUser((int)$a['id']) ?: $s
);

$app->get('/admin/projects', function(Request $r, Response $s){
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Projectbeheer',(new AdminProjectController())->index()));
    return $s;
});

$app->post('/admin/projects/add',[new AdminProjectController(),'add']);
$app->get('/admin/projects/edit/{id}',[new AdminProjectController(),'edit']);
$app->post('/admin/projects/update/{id}',[new AdminProjectController(),'update']);
$app->get('/admin/projects/delete/{id}',[new AdminProjectController(),'delete']);

$app->get('/admin/inbox', function(Request $r, Response $s){
    if ($resp = requireAdmin($s)) return $resp;
    $s->getBody()->write(renderLayout('Inbox',(new AdminContactController())->index()));
    return $s;
});

$app->run();
