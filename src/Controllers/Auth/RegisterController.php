<?php
namespace App\Controllers\Auth;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegisterController {

    public function registerForm(): string {
        $error = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';

        $errorHtml = $error
            ? "<div class='flash flash--error'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"
            : '';

        $successHtml = $success
            ? "<div class='flash flash--success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"
            : '';

        return <<<HTML
<section class="auth-page">
  <div class="auth-card">
    <h1>Registreren</h1>
    <p class="auth-sub">
      Maak een account aan. Standaard word je geregistreerd als
      <strong>gebruiker</strong>.
    </p>

    {$errorHtml}
    {$successHtml}

    <form method="post" action="/register" class="auth-form">
      <label>Gebruikersnaam</label>
      <input name="username" autocomplete="username" required>

      <label>Wachtwoord</label>
      <input type="password" name="password" autocomplete="new-password" required>

      <button class="btn btn--primary" type="submit" style="margin-top:14px">
        Account aanmaken
      </button>
    </form>

    <div class="auth-footer">
      Heb je al een account? <a href="/login">Inloggen</a>
    </div>
  </div>
</section>
HTML;
    }

    public function register(Request $req, Response $res): Response {
        $d = $req->getParsedBody();
        $username = trim($d['username'] ?? '');
        $password = (string)($d['password'] ?? '');

        if ($username === '' || $password === '') {
            return $res
                ->withHeader("Location", "/register?error=Vul+alle+velden+in")
                ->withStatus(302);
        }

        if (User::where('username', $username)->first()) {
            return $res
                ->withHeader("Location", "/register?error=Gebruikersnaam+bestaat+al")
                ->withStatus(302);
        }

        User::create([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user'
        ]);

        return $res
            ->withHeader("Location", "/login?success=Account+aangemaakt.+Log+nu+in")
            ->withStatus(302);
    }
}
