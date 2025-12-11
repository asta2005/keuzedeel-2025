<?php
namespace App\Controllers;

use App\Models\Project;

class ProjectController {

    /**
     * Return inner HTML with a grid of projects.
     */
    public function index(): string {
        $projects = Project::all();

        $html = "<h1>Opdrachten en Projecten</h1>";
        $html .= "<div class=\"projects-grid\">";

        foreach ($projects as $project) {
            $html .= "<div class=\"project-card\">";

            if (!empty($project->img)) {
                // images are expected in public/img/
                $html .= "<img src=\"/img/{$project->img}\" alt=\"{$project->title}\">";
            }

            $html .= "<h3>{$project->title}</h3>";
            $html .= "<p class=\"project-description\">{$project->description}</p>";
            $html .= "<button type=\"button\">Meer</button>";
            $html .= "</div>";
        }

        $html .= "</div>";

        // small inline script to toggle card description visibility
        $html .= <<<SCRIPT
<script>
document.querySelectorAll('.project-card button').forEach(function(btn) {
    btn.addEventListener('click', function () {
        this.parentElement.classList.toggle('active');
    });
});
</script>
SCRIPT;

        return $html;
    }
}

