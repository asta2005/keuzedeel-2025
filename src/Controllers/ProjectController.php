<?php
namespace App\Controllers;

use App\Models\Project;

class ProjectController {

    public function index(): string {
        $projects = Project::orderBy('created_at','desc')->get();
        $html = '';

        foreach ($projects as $p) {
            $img = $p->img
                ? "<img src='/img/projects/{$p->img}' alt='{$p->title}'>"
                : "";

            $html .= "
            <article class='project-card'>
                {$img}
                <h3>{$p->title}</h3>
                <p>{$p->description}</p>
            </article>";
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
