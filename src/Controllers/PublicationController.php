<?php
namespace App\Controllers;

use App\Models\Publication;

class PublicationController {

    /**
     * Return inner HTML listing all publications.
     */
    public function index(): string {
        $publications = Publication::orderBy('created_at', 'desc')->get();

        $html = '<section class="page-wrapper">';
        $html .= '<header class="page-header"><h1>Publicaties</h1></header>';
        $html .= '<div class="page-content">';
        $html .= '<p>Bekijk hier een overzicht van rapporten, documenten en andere publicaties van PMB Amsterdam.</p>';
        $html .= '<div class="publication-list">';

        foreach ($publications as $pub) {
            $title = htmlspecialchars($pub->title, ENT_QUOTES, 'UTF-8');
            $slug  = htmlspecialchars($pub->slug ?? '', ENT_QUOTES, 'UTF-8');
            $rawContent = strip_tags($pub->content ?? '');
            $excerpt = substr($rawContent, 0, 160);
            if (strlen($rawContent) > 160) {
                $excerpt .= '...';
            }
            $excerptEsc = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');

            $html .= '<article class="publication-item">';
            $html .= "<h3>{$title}</h3>";
            if ($slug) {
                $html .= "<small>{$slug}</small>";
            }
            if ($excerptEsc) {
                $html .= "<p>{$excerptEsc}</p>";
            }
            $html .= '</article>';
        }

        $html .= '</div></div></section>';

        return $html;
    }
}

