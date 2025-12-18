<?php
namespace App\Controllers\Admin;

use App\Models\User;

class AdminUserController {

    private function adminOnly(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login?error=Geen+rechten");
            exit;
        }
    }

    public function index(): string {
        $this->adminOnly();

        $users = User::all();
        $rows = '';

        foreach ($users as $u) {
            $rows .= "
            <tr>
                <td>{$u->id}</td>
                <td>{$u->username}</td>
                <td>{$u->role}</td>
                <td>
                    <a href='/admin/user/admin/{$u->id}'>Admin maken</a> |
                    <a href='/admin/user/user/{$u->id}'>User maken</a>
                </td>
            </tr>";
        }

        return <<<HTML
<section class="admin-card">
<h1>Gebruikersbeheer</h1>

<table class="admin-table">
<tr>
    <th>ID</th>
    <th>Gebruiker</th>
    <th>Rol</th>
    <th>Actie</th>
</tr>
{$rows}
</table>
</section>
HTML;
    }

    public function makeAdmin(int $id): void {
        $this->adminOnly();
        User::where('id', $id)->update(['role' => 'admin']);
        header("Location: /admin/users");
        exit;
    }

    public function makeUser(int $id): void {
        $this->adminOnly();
        User::where('id', $id)->update(['role' => 'user']);
        header("Location: /admin/users");
        exit;
    }
}
