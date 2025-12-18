<?php
namespace App\Controllers\Admin;

use App\Models\ContactMessage;

class AdminContactController {

    private function adminOnly(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header("Location: /login");
            exit;
        }
    }

    public function index(): string {
        $this->adminOnly();

        $messages = ContactMessage::orderBy('created_at','desc')->get();

        $rows = '';
        foreach ($messages as $m) {
            $rows .= "
            <tr>
                <td>{$m->id}</td>
                <td>{$m->name}</td>
                <td>{$m->email}</td>
                <td>{$m->message}</td>
                <td>{$m->created_at}</td>
            </tr>";
        }

        if ($rows === '') {
            $rows = "<tr><td colspan='5' class='muted'>Geen berichten</td></tr>";
        }

        return <<<HTML
<section class="page-wrapper">
<h1>Contact inbox</h1>

<div class="admin-card">
<table class="admin-table">
<thead>
<tr>
    <th>ID</th>
    <th>Naam</th>
    <th>Email</th>
    <th>Bericht</th>
    <th>Datum</th>
</tr>
</thead>
<tbody>
{$rows}
</tbody>
</table>
</div>
</section>
HTML;
    }
}
