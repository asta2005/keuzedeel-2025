<?php
namespace App\Controllers\Auth;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegisterController {

    public function registerForm(): string {
        return <<<HTML
<style>
.register-box {
    max-width: 420px;
    margin: 80px auto;
    padding: 40px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

<div class="register-box">
<h2>Account aanmaken</h2>

<form method="post" action="/register">

    <label>Gebruikersnaam</label>
    <input name="username" required>

    <label>Wachtwoord</label>
    <input type="password" name="password" required>

    <button type="submit">Registreren</button>
</form>

<p><a href="/login">← Terug naar login</a></p>
</div>
HTML;
    }

    public function register(Request $request, Response $response): Response {

        $d = $request->getParsedBody();

        User::create([
            'username' => $d['username'],
            'password' => hash('sha256', $d['password']),
            'role' => 'user'
        ]);

        return $response->withHeader("Location", "/login")->withStatus(302);
    }
}
