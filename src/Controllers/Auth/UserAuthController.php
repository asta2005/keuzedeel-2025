<?php
namespace App\Controllers\Auth;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserAuthController {

    public function loginForm(): string {
        $error = $_GET['error'] ?? '';
        $errorHtml = $error
            ? "<div class='flash flash--error'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"
            : '';

        return <<<HTML
<section class="auth-page">
  <div class="auth-card">
    <h1>Inloggen</h1>
    <p class="auth-sub">Gebruik je PMB-account om toegang te krijgen.</p>

    {$errorHtml}

    <form method="post" action="/login" class="auth-form">
      <label>Gebruikersnaam</label>
      <input name="username" autocomplete="username" required>

      <label>Wachtwoord</label>
      <input type="password" name="password" autocomplete="current-password" required>

      <button class="btn btn--primary" type="submit">Inloggen</button>
    </form>

    <div class="auth-footer">
      Nog geen account? <a href="/register">Registreren</a>
    </div>
  </div>
</section>
HTML;
    }

    public function login(Request $req, Response $res): Response {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $d = $req->getParsedBody();
        $username = trim($d['username'] ?? '');
        $password = (string)($d['password'] ?? '');

        $user = User::where('username', $username)->first();

        // ✅ CORRECT password check
        if (!$user || !password_verify($password, $user->password)) {
            return $res
                ->withHeader(
                    "Location",
                    "/login?error=Onjuiste+gebruikersnaam+of+wachtwoord"
                )
                ->withStatus(302);
        }

        // Login success
        $_SESSION['user'] = $user->username;
        $_SESSION['role'] = $user->role;

        // Redirect based on role
        if (($user->role ?? 'user') === 'admin') {
            return $res->withHeader("Location", "/admin")->withStatus(302);
        }

        return $res->withHeader("Location", "/")->withStatus(302);
    }

    public function logout(Request $req, Response $res): Response {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        return $res->withHeader("Location", "/login")->withStatus(302);
    }
}
