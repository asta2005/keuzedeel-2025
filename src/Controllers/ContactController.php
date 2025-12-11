<?php
namespace App\Controllers;

class ContactController {

    public function index(): string {
        return <<<HTML
<section class="page-wrapper">
    <h1>Contact</h1>
    <p>Welkom op de contactpagina van PMB Amsterdam.</p>
    <p>Gebruik het formulier op deze pagina om ons te bereiken.</p>
</section>
HTML;
    }
}
