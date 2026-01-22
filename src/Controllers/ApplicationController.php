<?php
namespace App\Controllers;

use App\Models\Application;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApplicationController
{
    public function page(Request $request): string
    {
        // --- voorbeeld vacatures (hardcoded) ---
        $vacancies = [
            [
                'title' => 'Junior Projectondersteuner (Stage)',
                'dept'  => 'Projectmanagement',
                'hours' => '32–40 uur',
                'desc'  => 'Ondersteun projectleiders, werk aan planning, communicatie en documentatie binnen gemeentelijke projecten.',
            ],
            [
                'title' => 'Assistent Programmamanagement',
                'dept'  => 'Programmamanagement',
                'hours' => '36 uur',
                'desc'  => 'Help bij het bewaken van voortgang, risico’s en stakeholdercommunicatie in complexe programma’s.',
            ],
            [
                'title' => 'Medewerker Communicatie (Projecten)',
                'dept'  => 'Communicatie',
                'hours' => '24–32 uur',
                'desc'  => 'Schrijf updates, maak content en ondersteun bij communicatie rondom projecten en publicaties.',
            ],
        ];

        $cards = '';
        foreach ($vacancies as $v) {
            $t = htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8');
            $d = htmlspecialchars($v['dept'], ENT_QUOTES, 'UTF-8');
            $h = htmlspecialchars($v['hours'], ENT_QUOTES, 'UTF-8');
            $desc = htmlspecialchars($v['desc'], ENT_QUOTES, 'UTF-8');

            $cards .= <<<HTML
<article class="vacancy-card">
  <div class="vacancy-card__top">
    <h3>{$t}</h3>
    <div class="vacancy-meta">
      <span class="pill">{$d}</span>
      <span class="pill pill--muted">{$h}</span>
    </div>
  </div>
  <p class="vacancy-card__desc">{$desc}</p>
  <div class="vacancy-card__actions">
    <a class="apply-btn" href="#sollicitatie">Solliciteer</a>
  </div>
</article>
HTML;
        }

        $query = $request->getQueryParams();
        $flash = '';
        if (($query['sent'] ?? null) === '1') {
            $flash = '<div class="flash flash--success">✅ Bedankt! Je sollicitatie is verstuurd.</div>';
        } elseif (($query['sent'] ?? null) === '0') {
            $flash = '<div class="flash flash--error">❌ Er ging iets mis. Probeer opnieuw.</div>';
        }

        return <<<HTML
<section class="page-wrapper">
  <div class="page-head">
    <h1>Werken bij PMB Amsterdam</h1>
    <p class="muted">Bekijk onze vacatures en solliciteer direct via het formulier.</p>
  </div>

  <div class="vacancies-grid">
    {$cards}
  </div>

  <div class="apply-section" id="sollicitatie">
    <h2>Solliciteren</h2>
    <p class="muted">Vul je gegevens in en upload je CV (PDF).</p>

    {$flash}

    <form class="apply-form" method="post" action="/solliciteer" enctype="multipart/form-data">
      <div class="form-grid">
        <div>
          <label for="name">Naam</label>
          <input id="name" type="text" name="name" required autocomplete="name">
        </div>

        <div>
          <label for="email">E-mail</label>
          <input id="email" type="email" name="email" required autocomplete="email">
        </div>

        <div>
          <label for="phone">Telefoonnummer</label>
          <input id="phone" type="text" name="phone" required autocomplete="tel">
        </div>

        <div>
          <label for="cv">Upload CV (PDF)</label>
          <input id="cv" type="file" name="cv" accept="application/pdf" required>
        </div>
      </div>

      <label for="motivation">Motivatie</label>
      <textarea id="motivation" name="motivation" rows="5" required></textarea>

      <button class="btn btn--primary" type="submit">Versturen</button>
    </form>
  </div>
</section>
HTML;
    }

    public function submit(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles();

        // Basis validatie
        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $motivation = trim((string)($data['motivation'] ?? ''));

        if ($name === '' || $email === '' || $phone === '' || $motivation === '') {
            return $response->withHeader('Location', '/werken-bij?sent=0#sollicitatie')->withStatus(302);
        }

        // Upload CV
        $cvFile = $files['cv'] ?? null;
        if (!$cvFile || $cvFile->getError() !== UPLOAD_ERR_OK) {
            return $response->withHeader('Location', '/werken-bij?sent=0#sollicitatie')->withStatus(302);
        }

        // Alleen PDF toestaan (light check)
        $clientName = $cvFile->getClientFilename() ?? 'cv.pdf';
        $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return $response->withHeader('Location', '/werken-bij?sent=0#sollicitatie')->withStatus(302);
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($clientName, PATHINFO_FILENAME));
        $filename = uniqid('cv_', true) . '_' . $safeBase . '.pdf';

        // Doelmap: public/uploads
        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $cvFile->moveTo($uploadDir . '/' . $filename);

        // Opslaan in database (tabel: applications)
        Application::create([
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'motivation' => $motivation,
            'cv_file'    => $filename,
        ]);

        return $response->withHeader('Location', '/werken-bij?sent=1#sollicitatie')->withStatus(302);
    }
}
