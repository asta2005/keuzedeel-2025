<?php
namespace App\Controllers\Admin;

use App\Models\Admin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminAuthController {

    public function loginForm(): string {

        $error = $_GET['error'] ?? '';

        $errorBox = $error ? "
            <div class='popup-error' id='popup'>{$error}</div>
            <script>
                setTimeout(() => {
                    document.getElementById('popup').classList.add('show');
                    setTimeout(() => {
                        document.getElementById('popup').classList.remove('show');
                    }, 3000);
                }, 200);
            </script>
        " : "";

        return <<<HTML
<style>
.login-box {
    max-width: 420px;
    margin: 60px auto;
    padding: 40px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    font-family: Arial;
}
.login-box input, .login-box button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 6px;
    border: 1px solid #bbb;
}
.login-box button {
    background: #e30613;
    border: none;
    color: white;
    cursor: pointer;
}
.login-box button:hover { background: #b2050f; }

.popup-error {
    position: fixed;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    background: #e30613;
    color: #fff;
    padding: 15px 25px;
    border-radius: 6px;
    transition: top 0.3s ease;
}
.popup-error.show { top: 30px; }
</style>

{$errorBox}

<div class="login-box">
    <h2>Admin Login</h2>

    <form method="post" action="/admin/login">
        <label>Gebruikersnaam</label>
        <input type="text" name="username" required>

        <label>Wachtwoord</label>
        <input type="password" name="password" required>

        <button type="submit">Inloggen</button>
    </form>

    <p style="text-align:center;margin-top:10px;">
        <a href="/admin/register">Account maken</a>
    </p>
</div>
HTML;
    }

    public function login(Request $request, Response $response): Response {

        session_start();

        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $admin = Admin::where('username', $username)->first();

        // FOUT: gebruiker bestaat niet
        if (!$admin) {
            return $response->withHeader("Location", "/admin/login?error=Onbekende+gebruiker")->withStatus(302);
        }

        // FOUT: wachtwoord klopt niet (GEEN hash)
        if ($password !== $admin->password) {
            return $response->withHeader("Location", "/admin/login?error=Onjuist+wachtwoord")->withStatus(302);
        }

        // SUCCES
        $_SESSION['admin'] = $admin->username;

        return $response->withHeader("Location", "/admin")->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response {
        session_start();
        session_destroy();

        return $response->withHeader("Location", "/admin/login")->withStatus(302);
    }
}
