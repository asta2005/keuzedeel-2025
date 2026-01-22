<?php
namespace App\Controllers\Admin;

use App\Models\Application;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminApplicationController
{
    private function adminOnly(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    /* ============================
       INDEX: lijst sollicitaties
    ============================ */
    public function index(): string
    {
        $this->adminOnly();

        $apps = Application::orderBy('created_at', 'desc')->get();

        $rows = '';
        foreach ($apps as $a) {
            $id = (int)$a->id;
            $name = htmlspecialchars((string)$a->name, ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars((string)$a->email, ENT_QUOTES, 'UTF-8');
            $phone = htmlspecialchars((string)$a->phone, ENT_QUOTES, 'UTF-8');
            $motivation = nl2br(htmlspecialchars((string)$a->motivation, ENT_QUOTES, 'UTF-8'));
            $created = htmlspecialchars((string)($a->created_at ?? ''), ENT_QUOTES, 'UTF-8');

            $cv = $a->cv_file ? htmlspecialchars((string)$a->cv_file, ENT_QUOTES, 'UTF-8') : '';

            $cvBtn = $cv
                ? "<a class='btn btn--sm btn--primary' href='/admin/sollicitaties/cv/{$id}'>CV downloaden</a>"
                : "<span class='muted'>Geen CV</span>";

            $rows .= "
            <tr>
              <td><strong>{$name}</strong><br><span class='muted'>{$created}</span></td>
              <td>{$email}<br><span class='muted'>{$phone}</span></td>
              <td style='max-width:520px'>{$motivation}</td>
              <td>{$cvBtn}</td>
            </tr>";
        }

        if (!$rows) {
            $rows = "<tr><td colspan='4' class='muted' style='padding:18px'>Nog geen sollicitaties.</td></tr>";
        }

        return <<<HTML
<section class="page-wrapper">
  <h1>Sollicitaties</h1>

  <div class="admin-panel inbox-panel" style="margin-top:18px">
    <table class="inbox-table">
      <thead>
        <tr>
          <th>Sollicitant</th>
          <th>Contact</th>
          <th>Motivatie</th>
          <th>CV</th>
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

    /* ============================
       DOWNLOAD CV
    ============================ */
    public function downloadCv(Request $req, Response $res, array $args): Response
    {
        $this->adminOnly();

        $id = (int)($args['id'] ?? 0);
        $app = Application::findOrFail($id);

        if (empty($app->cv_file)) {
            $res->getBody()->write("Geen CV gevonden.");
            return $res->withStatus(404);
        }

        // ✅ Pad waar je CV's opslaat (zoals in jouw ApplicationController screenshot)
        $filename = basename((string)$app->cv_file);
        $filePath = __DIR__ . '/../../../public/uploads/' . $filename;

        if (!is_file($filePath)) {
            $res->getBody()->write("Bestand niet gevonden op server.");
            return $res->withStatus(404);
        }

        $mime = mime_content_type($filePath) ?: 'application/octet-stream';

        $stream = fopen($filePath, 'rb');
        $body = $res->getBody();
        while (!feof($stream)) {
            $body->write(fread($stream, 8192));
        }
        fclose($stream);

        return $res
            ->withHeader('Content-Type', $mime)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Length', (string) filesize($filePath));
    }
}
