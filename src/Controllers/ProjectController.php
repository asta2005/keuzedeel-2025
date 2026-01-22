<?php
namespace App\Controllers;

use App\Models\Project;

class ProjectController
{
    public function index(): string
    {
        $projects = Project::orderBy('created_at', 'desc')->get();

        $publicRoot = realpath(__DIR__ . '/../../public'); // /public map
        $html = '';

        foreach ($projects as $p) {
            $title = htmlspecialchars((string)($p->title ?? ''), ENT_QUOTES, 'UTF-8');
            $desc  = nl2br(htmlspecialchars((string)($p->description ?? ''), ENT_QUOTES, 'UTF-8'));

            // ✅ Pak eerst "image", anders "img" (oude naam)
            $filename = $p->image ?? $p->img ?? null;
            $imgHtml = '';

            if (!empty($filename)) {
                $safeFile = basename((string)$filename);

                // ✅ Zoek waar het bestand echt staat
                $src = null;

                if ($publicRoot && is_file($publicRoot . '/img/projects/' . $safeFile)) {
                    $src = '/img/projects/' . $safeFile;
                } elseif ($publicRoot && is_file($publicRoot . '/img/' . $safeFile)) {
                    $src = '/img/' . $safeFile;
                } elseif ($publicRoot && is_file($publicRoot . '/uploads/' . $safeFile)) {
                    $src = '/uploads/' . $safeFile;
                } else {
                    // fallback: probeer /img/projects eerst (voor het geval file bestaat maar check faalt op hosting)
                    $src = '/img/projects/' . $safeFile;
                }

                $alt = $title !== '' ? $title : 'Project afbeelding';

                $imgHtml = <<<HTML
<img
  src="{$src}"
  alt="{$alt}"
  loading="lazy"
  decoding="async"
  width="360"
  height="180"
/>
HTML;
            }

            $html .= <<<HTML
<article class="project-card">
  {$imgHtml}
  <div class="project-card__body">
    <h3>{$title}</h3>
    <p>{$desc}</p>
  </div>
</article>
HTML;
        }

        if (trim($html) === '') {
            $html = "<p class='muted'>Nog geen projecten gevonden.</p>";
        }

        return <<<HTML
<section class="page-wrapper">
  <h1>Opdrachten en projecten</h1>

  <div class="projects-grid">
    {$html}
  </div>
</section>
HTML;
    }
}
