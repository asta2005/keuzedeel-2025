<?php
namespace App\Controllers\Auth;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserAuthController {

    public function loginForm(): string {

        $error = $_GET['error'] ?? '';

        return <<<HTML
<style>
.login-box {
    max-width: 420px;
    margin: 80px auto;
    padding: 40px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #d1d1d1;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    font-family: Arial;
}
.login-box h2 { text-align: center; }
.login-box input, .login-box button {
    width: 100%; padding: 12px;
    margin-top: 10px; border-radius: 6px;
}
.login-box button {
    background: #0a5fa3; color: white; border: none;
}
.error { color: #d60000; margin-bottom: 10px; text-align:center; }
</style>

<div class="login-box">

<h2>Inloggen</h2>

<p class="error">{$error}</p>

<form method="post" action="/login">

    <label>Gebruikersnaam</label>
    <input name="username" required>

    <label>Wachtwoord</label>
    <input name="password" type="password" required>

    <button type="submit">Inloggen</button>

</form>

<p style="text-align:center;margin-top:10px;">
    <a href="/register">Account aanmaken</a>
</p>

</div>
HTML;
    }


    public function login(Request $request, Response $response): Response {
        session_start();

        $data = $request->getParsedBody();

        $user = User::where('username', $data['username'])->first();

        if (!$user || hash('sha256', $data['password']) !== $user->password) {
            return $response->withHeader("Location", "/login?error=Onjuiste+gegevens")->withStatus(302);
        }

        // save session
        $_SESSION['user'] = $user->username;
        $_SESSION['role'] = $user->role;

        // redirect based on role
        if ($user->role === 'admin') {
            return $response->withHeader("Location", "/admin")->withStatus(302);
        } else {
            return $response->withHeader("Location", "/dashboard")->withStatus(302);
        }
    }


    public function logout(Request $request, Response $response): Response {
        session_start();
        session_destroy();

        return $response->withHeader("Location", "/login")->withStatus(302);
    }
}
