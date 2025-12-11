<?php
namespace App\Controllers;

class HomeController {
    public function index(): string {
        return <<<HTML

    <!-- HERO SLIDESHOW -->
    <section class="hero animated-hero">
        <div class="hero-overlay"></div>
        <div class="hero-inner">
            <h1>Welkom bij PMB Amsterdam</h1>
            <p>Onze missie is project-, programma- en procesmanagement in de stad. Bekijk hieronder meer over onze projecten, expertise en publicaties.</p>
        </div>
    </section>

    <!-- JOUW BESTAANDE CONTAINERS -->
    <div class="containers">
        <div class="container-card">
            <img src="/img/gem1.png" alt="Projecten">
            <h3>Projecten</h3>
            <p>Bekijk onze lopende en afgeronde projecten.</p>
            <a href="/opdrachten-en-projecten">Meer</a>
        </div>

        <div class="container-card">
            <img src="/img/gem2.png" alt="Expertise">
            <h3>Expertise</h3>
            <p>Ontdek onze vakgebieden en specialisaties.</p>
            <a href="/expertise">Meer</a>
        </div>

        <div class="container-card">
            <img src="/img/gem3.png" alt="Publicaties">
            <h3>Publicaties</h3>
            <p>Bekijk onze rapporten, documenten en publicaties.</p>
            <a href="/publicaties">Meer</a>
        </div>
    </div>

HTML;
    }
}
