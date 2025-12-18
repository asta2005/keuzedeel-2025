<?php
namespace App\Controllers\Admin;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminProjectController {

    private function adminOnly(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login?error=Geen+rechten");
            exit;
        }
    }

    /* ============================
       INDEX
    ============================ */
    public function index(): string {
        $this->adminOnly();

        $projects = Project::orderBy('created_at','desc')->get();
        $rows = '';

        foreach ($projects as $p) {
            $img = $p->img
                ? "<img src='/img/projects/{$p->img}' style='height:40px;border-radius:6px'>"
                : "<span class='muted'>Geen</span>";

            $rows .= "
            <tr>
                <td>{$p->id}</td>
                <td>{$p->title}</td>
                <td>{$img}</td>
                <td class='td-actions'>
                    <a class='btn btn--sm btn--ghost' href='/admin/projects/edit/{$p->id}'>✏️ Bewerken</a>
                    <a class='btn btn--sm btn--danger' href='/admin/projects/delete/{$p->id}' onclick='return confirm(\"Verwijderen?\")'>🗑️</a>
                </td>
            </tr>";
        }

        return <<<HTML
<section class="page-wrapper">
<h1>Projectbeheer</h1>

<div class="admin-panel">
<h3 class="admin-panel__title">Bestaande projecten</h3>

<table class="admin-table">
<thead>
<tr>
    <th>ID</th>
    <th>Titel</th>
    <th>Afbeelding</th>
    <th>Acties</th>
</tr>
</thead>
<tbody>
{$rows}
</tbody>
</table>
</div>

<div class="admin-panel" style="margin-top:20px">
<h3 class="admin-panel__title">Nieuw project toevoegen</h3>

<form method="post" action="/admin/projects/store" enctype="multipart/form-data" class="form">
    <label>Titel</label>
    <input name="title" required>

    <label>Beschrijving</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Afbeelding uploaden</label>
    <input type="file" name="img" accept="image/*">

    <button class="btn btn--primary" style="margin-top:12px">Opslaan</button>
</form>
</div>
</section>
HTML;
    }

    /* ============================
       STORE
    ============================ */
    public function store(Request $req, Response $res): Response {
        $this->adminOnly();

        $data = $req->getParsedBody();
        $filename = null;

        if (!empty($_FILES['img']['name'])) {
            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('project_') . '.' . $ext;

            move_uploaded_file(
                $_FILES['img']['tmp_name'],
                __DIR__ . '/../../../public/img/projects/' . $filename
            );
        }

        Project::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'img' => $filename
        ]);

        $_SESSION['flash'] = "Project toegevoegd";
        return $res->withHeader("Location","/admin/projects")->withStatus(302);
    }

    /* ============================
       EDIT
    ============================ */
    public function edit(int $id): string {
        $this->adminOnly();
        $p = Project::findOrFail($id);

        $preview = $p->img
            ? "<img src='/img/projects/{$p->img}' style='max-height:120px;border-radius:10px;margin-bottom:10px'>"
            : "<p class='muted'>Geen afbeelding</p>";

        return <<<HTML
<section class="page-wrapper">
<h1>Project bewerken</h1>

<div class="admin-panel">
{$preview}

<form method="post" action="/admin/projects/update/{$p->id}" enctype="multipart/form-data" class="form">

    <label>Titel</label>
    <input name="title" value="{$p->title}" required>

    <label>Beschrijving</label>
    <textarea name="description" rows="4" required>{$p->description}</textarea>

    <label>Nieuwe afbeelding (optioneel)</label>
    <input type="file" name="img" accept="image/*">

    <button class="btn btn--primary" style="margin-top:12px">Opslaan</button>
</form>
</div>
</section>
HTML;
    }

    /* ============================
       UPDATE
    ============================ */
    public function update(Request $req, Response $res, array $args): Response {
        $this->adminOnly();

        $data = $req->getParsedBody();
        $p = Project::findOrFail($args['id']);

        $filename = $p->img;

        if (!empty($_FILES['img']['name'])) {
            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('project_') . '.' . $ext;

            move_uploaded_file(
                $_FILES['img']['tmp_name'],
                __DIR__ . '/../../../public/img/projects/' . $filename
            );
        }

        $p->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'img' => $filename
        ]);

        $_SESSION['flash'] = "Project bijgewerkt";
        return $res->withHeader("Location","/admin/projects")->withStatus(302);
    }

    /* ============================
       DELETE
    ============================ */
    public function delete(Request $req, Response $res, array $args): Response {
        $this->adminOnly();

        Project::destroy($args['id']);
        $_SESSION['flash'] = "Project verwijderd";

        return $res->withHeader("Location","/admin/projects")->withStatus(302);
    }
}
