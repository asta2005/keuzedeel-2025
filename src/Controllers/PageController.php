<?php
namespace App\Controllers;

use App\Models\Page;

class PageController {

    /**
     * Return inner HTML for a page by slug.
     */
    public function showBySlug(string $slug): string {
        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            return "<section class=\"page-wrapper\"><div class=\"page-header\"><h1>Pagina niet gevonden</h1></div><div class=\"page-content\"><p>De opgevraagde pagina kon niet worden gevonden.</p></div></section>";
        }

        $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
        $content = $page->content; // allow basic HTML from beheeromgeving
        $hero = '';

        if (!empty($page->img)) {
            $imgUrl = htmlspecialchars($page->img, ENT_QUOTES, 'UTF-8');
            $hero = "<div class=\"page-hero\" style=\"background-image:url('{$imgUrl}')\"></div>";
        }

        return <<<HTML
<section class="page-wrapper">
    <header class="page-header">
        <h1>{$title}</h1>
    </header>
    {$hero}
    <div class="page-content">
        {$content}
    </div>
</section>
HTML;
    }
}

