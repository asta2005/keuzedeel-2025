<?php
namespace App\Controllers\Admin;

use App\Models\Admin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminRegisterController {

    public function registerForm(): string {

        $error = $_GET['error'] ?? '';
        $success = $_GET['success'] ?? '';

        $errorBox = $error ? "
            <div class='popup-error' id='errorPopup'>{$error}</div>
            <script>
                setTimeout(() => {
                    document.getElementById('errorPopup').classList.add('show');
                    setTimeout(() => {
                        document.getElementById('errorPopup').classList.remove('show');
                    }, 3000);
                }, 200);
            </script>
        " : "";

        $successBox = $success ? "
            <div class='popup-success' id='successPopup'>{$success}</div>
            <script>
                setTimeout(() => {
                    document.getElementById('successPopup').classList.add('show');
                    setTimeout(() => {
                        document.getElementById('successPopup').classList.remove('show');
                    }, 3000);
                }, 200);
            </script>
        " : "";

        return <<<HTML
<style>

.register-box {
    max-width: 420px;
    margin: 70px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    animation: fadeIn 0.4s ease;
    font-family: Arial, sans-serif;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.register-box h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}

.register-box label {
    font-weight: bold;
    margin-top: 10px;
    display: block;
}

.register-box input {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #cfcfcf;
    margin-bottom: 15px;
    font-size: 1rem;
}

.register-box button {
    width: 100%;
    background: #0a7e2f;
    border: none;
    padding: 12px;
    border-radius: 6px;
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
}

.register-box button:hover {
    background: #066822;
}

/* POPUP BOXES */

.popup-error,
.popup-success {
    position: fixed;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    padding: 15px 25px;
    color: #fff;
    border-radius: 6px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.3);
    font-size: 1rem;
    transition: top 0.4s ease;
    z-index: 9999;
}

.popup-error { background: #e30613; }
.popup-success { background: #0a7e2f; }

.popup-error.show,
.popup-success.show { top: 25px; }

</style>

{$errorBox}
{$successBox}

<section class="register-box">
<h1>Admin Registratie</h1>

<form method="post" action="/admin/register">

    <label>Gebruikersnaam</label>
    <input type="text" name="username" required>

    <label>Wachtwoord</label>
    <input type="password" name="password" required>

    <label>Bevestig wachtwoord</label>
    <input type="password" name="password_confirm" required>

    <button>Registreren</button>

</form>

<p style="text-align:center; margin-top:15px;">
    <a href="/admin/login">← Terug naar login</a>
</p>

</section>
HTML;
    }


    public function register(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        $username = trim($data['username']);
        $password = $data['password'];
        $confirm  = $data['password_confirm'];

        // Check of de gebruiker al bestaat
        if (Admin::where('username', $username)->first()) {
            return $response->withHeader(
                "Location",
                "/admin/register?error=Gebruikersnaam+bestaat+al"
            )->withStatus(302);
        }

        // Check wachtwoorden
        if ($password !== $confirm) {
            return $response->withHeader(
                "Location",
                "/admin/register?error=Wachtwoorden+matchen+niet"
            )->withStatus(302);
        }

        // Nieuwe gebruiker aanmaken → standaard rol = 0
        Admin::create([
            'username' => $username,
            'password' => hash('sha256', $password),
            'role' => 0
        ]);

        // Success popup
        return $response->withHeader(
            "Location",
            "/admin/register?success=Account+succesvol+aangemaakt"
        )->withStatus(302);
    }
}
