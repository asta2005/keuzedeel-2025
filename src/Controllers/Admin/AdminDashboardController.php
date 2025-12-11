<?php
namespace App\Controllers\Admin;

class AdminDashboardController {

    public function index(): string {

        return <<<HTML
<style>
.admin-box {
    max-width: 900px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    font-family: Arial;
}
.admin-box h1 {
    margin-bottom: 15px;
}
.admin-menu a {
    display: block;
    padding: 12px 15px;
    background: #0a5fa3;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 10px;
    width: 280px;
}
.admin-menu a:hover {
    background: #084c82;
}
</style>

<div class="admin-box">
    <h1>Welkom in het Admin Panel</h1>
    <p>Kies een onderdeel:</p>

    <div class="admin-menu">
        <a href="/admin/users">Gebruikers beheren</a>
        <a href="/admin/projects">Projecten beheren</a>
        <a href="/logout">Uitloggen</a>
    </div>
</div>
HTML;
    }
}
