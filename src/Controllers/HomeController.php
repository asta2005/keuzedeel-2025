<?php
namespace App\Controllers;

class HomeController {
    public function index(): string {
        return <<<HTML

<!-- HERO (LCP FIX: echte img + fetchpriority high, geen lazy) -->
<section class="hero hero--lcp" aria-label="PMB Amsterdam introductie">
    <img
        class="hero__img"
        src="/img/gem1.webp"
        alt="PMB Amsterdam – Gemeente Amsterdam"
        width="1200"
        height="350"
        fetchpriority="high"
        decoding="async"
    >
    <div class="hero-overlay"></div>
    <div class="hero-inner">
        <h1>Welkom bij PMB Amsterdam</h1>
        <p>Onze missie is project-, programma- en procesmanagement in de stad. Bekijk hieronder meer over onze projecten, expertise en publicaties.</p>
    </div>
</section>

<!-- CARDS -->
<div class="containers">

    <div class="container-card">
        <picture>
            <source
                type="image/webp"
                srcset="
                    /img/gem1.webp 480w,
                    /img/gem1.webp 768w,
                    /img/gem1.webp 1200w
                "
                sizes="(max-width: 768px) 100vw, 360px"
            >
            <img
                src="/img/gem1-768.webp"
                alt="Projecten van PMB Amsterdam in de stad Amsterdam"
                width="360"
                height="160"
                loading="lazy"
                decoding="async"
            >
        </picture>
        <h3>Projecten</h3>
        <p>Bekijk onze lopende en afgeronde projecten.</p>
        <a href="/opdrachten-en-projecten">Meer</a>
    </div>

    <div class="container-card">
        <picture>
            <source
                type="image/webp"
                srcset="
                    /img/gem2.webp 480w,
                    /img/gem2.webp 768w,
                    /img/gem2.webp 1200w
                "
                sizes="(max-width: 768px) 100vw, 360px"
            >
            <img
                src="/img/gem2-768.webp"
                alt="Expertise en vakgebieden van PMB Amsterdam"
                width="360"
                height="160"
                loading="lazy"
                decoding="async"
            >
        </picture>
        <h3>Expertise</h3>
        <p>Ontdek onze vakgebieden en specialisaties.</p>
        <a href="/expertise">Meer</a>
    </div>

    <div class="container-card">
        <picture>
            <source
                type="image/webp"
                srcset="
                    /img/gem3.webp 480w,
                    /img/gem3.webp 768w,
                    /img/gem3.webp 1200w
                "
                sizes="(max-width: 768px) 100vw, 360px"
            >
            <img
                src="/img/gem3-768.webp"
                alt="Publicaties en rapporten van PMB Amsterdam"
                width="360"
                height="160"
                loading="lazy"
                decoding="async"
            >
        </picture>
        <h3>Publicaties</h3>
        <p>Bekijk onze rapporten, documenten en publicaties.</p>
        <a href="/publicaties">Meer</a>
    </div>

</div>

HTML;
    }
}
