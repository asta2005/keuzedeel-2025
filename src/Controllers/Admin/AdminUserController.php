<?php
namespace App\Controllers\Admin;

use App\Models\User;

class AdminUserController {

    private function requireAdmin() {
        session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login?error=Geen+toegang");
            exit;
        }
    }

    public function list(): string {
        $this->requireAdmin();

        $users = User::all();

        $rows = "";
        foreach ($users as $u) {
            $rows .= "
            <tr>
                <td>{$u->id}</td>
                <td>{$u->username}</td>
                <td>{$u->role}</td>
                <td>
                    <a href='/admin/user/make-admin/{$u->id}'>Admin maken</a> |
                    <a href='/admin/user/make-user/{$u->id}'>User maken</a>
                </td>
            </tr>";
        }

        return <<<HTML
<h1>Gebruikersbeheer</h1>
<table border='1' cellpadding='8'>
<tr><th>ID</th><th>Gebruiker</th><th>Rol</th><th>Actie</th></tr>
{$rows}
</table>
HTML;
    }

    public function promote($id) {
        $this->requireAdmin();
        User::where('id',$id)->update(['role'=>'admin']);
        header("Location: /admin/users");
        exit;
    }
    
    public function demote($id) {
        $this->requireAdmin();
        User::where('id',$id)->update(['role'=>'user']);
        header("Location: /admin/users");
        exit;
    }
}
