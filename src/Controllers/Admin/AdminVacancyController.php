<?php
namespace App\Controllers\Admin;

use App\Models\Vacancy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminVacancyController
{
    private function adminOnly(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    public function index(): string
    {
        $this->adminOnly();

        $vacancies = Vacancy::orderBy('id', 'desc')->get();
        $rows = '';

        foreach ($vacancies as $v) {
            $title = htmlspecialchars($v->title ?? '', ENT_QUOTES, 'UTF-8');
            $location = htmlspecialchars($v->location ?? '', ENT_QUOTES, 'UTF-8');
            $type = htmlspecialchars($v->type ?? '', ENT_QUOTES, 'UTF-8');

            $rows .= "
            <tr>
              <td>{$v->id}</td>
              <td>{$title}</td>
              <td>{$location}</td>
              <td>{$type}</td>
              <td class='td-actions'>
                <a class='btn btn--sm btn--ghost' href='/admin/vacatures/edit/{$v->id}'>✏️ Bewerken</a>
                <a class='btn btn--sm btn--danger' href='/admin/vacatures/delete/{$v->id}' onclick='return confirm(\"Verwijderen?\")'>🗑️</a>
              </td>
            </tr>";
        }

        return <<<HTML
<section class="page-wrapper">
  <h1>Vacatures beheren</h1>

  <div class="admin-panel">
    <h3 class="admin-panel__title">Bestaande vacatures</h3>

    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titel</th>
          <th>Locatie</th>
          <th>Type</th>
          <th>Acties</th>
        </tr>
      </thead>
      <tbody>
        {$rows}
      </tbody>
    </table>
  </div>

  <div class="admin-panel" style="margin-top:20px">
    <h3 class="admin-panel__title">Nieuwe vacature toevoegen</h3>

    <form method="post" action="/admin/vacatures/store" class="form">
      <label>Titel</label>
      <input name="title" required>

      <label>Locatie</label>
      <input name="location" placeholder="Amsterdam" required>

      <label>Type</label>
      <input name="type" placeholder="Fulltime / Parttime / Stage" required>

      <label>Omschrijving</label>
      <textarea name="description" rows="5" required></textarea>

      <button class="btn btn--primary" style="margin-top:12px">Opslaan</button>
    </form>
  </div>
</section>
HTML;
    }

    public function store(Request $req, Response $res): Response
    {
        $this->adminOnly();

        $data = (array)$req->getParsedBody();

        Vacancy::create([
            'title' => trim($data['title'] ?? ''),
            'location' => trim($data['location'] ?? ''),
            'type' => trim($data['type'] ?? ''),
            'description' => trim($data['description'] ?? ''),
        ]);

        return $res->withHeader('Location', '/admin/vacatures')->withStatus(302);
    }

    public function edit(Request $req, Response $res, array $args): Response
    {
        $this->adminOnly();

        $v = Vacancy::findOrFail((int)$args['id']);

        $title = htmlspecialchars($v->title ?? '', ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars($v->location ?? '', ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($v->type ?? '', ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($v->description ?? '', ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
<section class="page-wrapper">
  <h1>Vacature bewerken</h1>

  <div class="admin-panel">
    <form method="post" action="/admin/vacatures/update/{$v->id}" class="form">
      <label>Titel</label>
      <input name="title" value="{$title}" required>

      <label>Locatie</label>
      <input name="location" value="{$location}" required>

      <label>Type</label>
      <input name="type" value="{$type}" required>

      <label>Omschrijving</label>
      <textarea name="description" rows="6" required>{$desc}</textarea>

      <button class="btn btn--primary" style="margin-top:12px">Opslaan</button>
      <a class="btn" style="margin-left:8px" href="/admin/vacatures">Annuleren</a>
    </form>
  </div>
</section>
HTML;

        $res->getBody()->write($html);
        return $res;
    }

    public function update(Request $req, Response $res, array $args): Response
    {
        $this->adminOnly();

        $v = Vacancy::findOrFail((int)$args['id']);
        $data = (array)$req->getParsedBody();

        $v->update([
            'title' => trim($data['title'] ?? ''),
            'location' => trim($data['location'] ?? ''),
            'type' => trim($data['type'] ?? ''),
            'description' => trim($data['description'] ?? ''),
        ]);

        return $res->withHeader('Location', '/admin/vacatures')->withStatus(302);
    }

    public function delete(Request $req, Response $res, array $args): Response
    {
        $this->adminOnly();

        Vacancy::destroy((int)$args['id']);
        return $res->withHeader('Location', '/admin/vacatures')->withStatus(302);
    }
}
