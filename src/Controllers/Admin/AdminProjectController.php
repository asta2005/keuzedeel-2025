<?php
namespace App\Controllers\Admin;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminProjectController
{
    private function adminOnly(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    private function uploadDir(): string {
        // public/uploads/projects/
        return __DIR__ . '/../../../public/uploads/projects/';
    }

    private function ensureUploadDir(): void {
        $dir = $this->uploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    private function sanitizeFilename(string $name): string {
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        return $name ?: ('file_' . time());
    }

    /* ============================
       INDEX: lijst + toevoegen
    ============================ */
    public function index(): string
    {
        $this->adminOnly();

        $projects = Project::orderBy('id', 'desc')->get();

        $cards = '';
        foreach ($projects as $p) {
            $id = (int)$p->id;
            $title = htmlspecialchars((string)$p->title, ENT_QUOTES, 'UTF-8');
            $desc  = htmlspecialchars((string)$p->description, ENT_QUOTES, 'UTF-8');

            $imgHtml = '';
            if (!empty($p->image)) {
                $img = htmlspecialchars((string)$p->image, ENT_QUOTES, 'UTF-8');
                $imgHtml = "<img src=\"/uploads/projects/{$img}\" alt=\"{$title}\" loading=\"lazy\" decoding=\"async\">";
            } else {
                $imgHtml = "<div class=\"muted\" style=\"padding:10px 0\">Geen afbeelding</div>";
            }

            $cards .= <<<HTML
<div class="admin-project-card">
  {$imgHtml}
  <h3>{$title}</h3>
  <p class="muted">{$desc}</p>
  <div class="admin-project-actions">
    <a class="btn btn--primary" href="/admin/projects/edit/{$id}">✏️ Bewerken</a>
    <a class="btn btn--primary" href="/admin/projects/delete/{$id}" onclick="return confirm('Weet je zeker dat je dit project wilt verwijderen?')">🗑️ Verwijderen</a>
  </div>
</div>
HTML;
        }

        if (!$cards) {
            $cards = '<p class="muted">Nog geen projecten.</p>';
        }

        return <<<HTML
<section class="page-wrapper">
  <h1>Projectbeheer</h1>
  <p class="muted">Projecten toevoegen, bewerken en verwijderen.</p>

  <div class="admin-panel" style="margin-top:18px">
    <h3 style="margin-top:0">Project toevoegen</h3>

    <form class="apply-form" method="post" action="/admin/projects/add" enctype="multipart/form-data">
      <label>Titel</label>
      <input type="text" name="title" required>

      <label>Beschrijving</label>
      <textarea name="description" rows="5" required></textarea>

      <label>Afbeelding (optioneel)</label>
      <input type="file" name="image" accept="image/*">

      <button class="btn btn--primary" type="submit" style="margin-top:10px">Toevoegen</button>
    </form>
  </div>

  <div class="admin-projects-grid" style="margin-top:22px">
    {$cards}
  </div>
</section>
HTML;
    }

    /* ============================
       ADD (POST)
    ============================ */
    public function add(Request $request, Response $response): Response
    {
        $this->adminOnly();
        $this->ensureUploadDir();

        $data = (array)$request->getParsedBody();
        $title = trim((string)($data['title'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));

        if ($title === '' || $description === '') {
            return $response->withHeader('Location', '/admin/projects')->withStatus(302);
        }

        $filename = null;
        $files = $request->getUploadedFiles();
        if (!empty($files['image']) && $files['image']->getError() === UPLOAD_ERR_OK) {
            $img = $files['image'];
            $orig = $this->sanitizeFilename((string)$img->getClientFilename());
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION)) ?: 'webp';
            $filename = 'project_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $img->moveTo($this->uploadDir() . $filename);
        }

        // ✅ DB: kolom "image" verwacht (pas aan als jouw kolom anders heet)
        Project::create([
            'title' => $title,
            'description' => $description,
            'image' => $filename,
        ]);

        return $response->withHeader('Location', '/admin/projects')->withStatus(302);
    }

    /* ============================
       EDIT PAGE (GET) -> string (wordt door index.php in renderLayout gezet)
    ============================ */
    public function editPage(int $id): string
    {
        $this->adminOnly();

        $p = Project::findOrFail($id);

        $title = htmlspecialchars((string)$p->title, ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars((string)$p->description, ENT_QUOTES, 'UTF-8');

        $preview = '<p class="muted">Geen afbeelding.</p>';
        if (!empty($p->image)) {
            $img = htmlspecialchars((string)$p->image, ENT_QUOTES, 'UTF-8');
            $preview = <<<HTML
<div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom:10px">
  <div style="max-width:380px; width:100%">
    <img src="/uploads/projects/{$img}" alt="{$title}" style="width:100%; height:auto; border-radius:14px">
    <div class="muted" style="margin-top:6px; font-size:.9rem">Huidige afbeelding</div>
  </div>
</div>
HTML;
        }

        return <<<HTML
<section class="page-wrapper">
  <h1>Project bewerken</h1>
  <p class="muted">Pas titel, beschrijving en (optioneel) de afbeelding aan.</p>

  <div class="admin-panel" style="margin-top:18px">
    {$preview}

    <form class="apply-form" method="post" action="/admin/projects/update/{$id}" enctype="multipart/form-data">
      <label>Titel</label>
      <input type="text" name="title" value="{$title}" required>

      <label>Beschrijving</label>
      <textarea name="description" rows="6" required>{$desc}</textarea>

      <label>Nieuwe afbeelding (optioneel)</label>
      <input type="file" name="image" accept="image/*">

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px">
        <button class="btn btn--primary" type="submit">Opslaan</button>
        <a class="btn btn--primary" href="/admin/projects" style="text-decoration:none; background:#e5e7eb; color:#111">Annuleren</a>
      </div>
    </form>
  </div>
</section>
HTML;
    }

    /* ============================
       UPDATE (POST)
    ============================ */
    public function update(Request $request, Response $response, array $args): Response
    {
        $this->adminOnly();
        $this->ensureUploadDir();

        $id = (int)($args['id'] ?? 0);
        $p = Project::findOrFail($id);

        $data = (array)$request->getParsedBody();
        $title = trim((string)($data['title'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));

        if ($title === '' || $description === '') {
            return $response->withHeader('Location', '/admin/projects/edit/' . $id)->withStatus(302);
        }

        // upload nieuwe afbeelding? -> overschrijf image
        $files = $request->getUploadedFiles();
        if (!empty($files['image']) && $files['image']->getError() === UPLOAD_ERR_OK) {
            $img = $files['image'];
            $orig = $this->sanitizeFilename((string)$img->getClientFilename());
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION)) ?: 'webp';
            $filename = 'project_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $img->moveTo($this->uploadDir() . $filename);

            // optioneel: oude verwijderen
            if (!empty($p->image)) {
                $old = $this->uploadDir() . basename((string)$p->image);
                if (is_file($old)) @unlink($old);
            }

            $p->image = $filename; // ✅ DB kolom image
        }

        $p->title = $title;
        $p->description = $description;
        $p->save();

        return $response->withHeader('Location', '/admin/projects')->withStatus(302);
    }

    /* ============================
       DELETE (GET)
    ============================ */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->adminOnly();

        $id = (int)($args['id'] ?? 0);
        $p = Project::findOrFail($id);

        // verwijder file mee
        if (!empty($p->image)) {
            $path = $this->uploadDir() . basename((string)$p->image);
            if (is_file($path)) @unlink($path);
        }

        $p->delete();

        return $response->withHeader('Location', '/admin/projects')->withStatus(302);
    }
}
