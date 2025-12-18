<?php
namespace App\Controllers\Admin;

class AdminDashboardController {

    public function index(): string
    {
        return <<<HTML
<section class="page-wrapper">

<div class="admin-shell">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="admin-brand">PMB Amsterdam</div>

        <a href="/admin" class="admin-nav admin-nav--active">📊 Dashboard</a>
        <a href="/admin/projects" class="admin-nav">📁 Projecten</a>
        <a href="/admin/users" class="admin-nav">👥 Gebruikers</a>
        <a href="/admin/inbox" class="admin-nav">✉️ Contact inbox</a>

        <a href="/logout" class="admin-nav admin-nav--out">⎋ Uitloggen</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <h1>Dashboard</h1>
        <p class="muted">
            Welkom in het beheerpaneel van <strong>PMB Amsterdam</strong>.
            Beheer hier projecten, gebruikers en binnengekomen berichten.
        </p>

        <!-- DASHBOARD CARDS -->
        <div class="admin-cards">

            <a href="/admin/projects" class="admin-card">
                <div class="admin-card__title">Projectbeheer</div>
                <div class="admin-card__desc">
                    Projecten toevoegen, bewerken en verwijderen.
                </div>
            </a>

            <a href="/admin/users" class="admin-card">
                <div class="admin-card__title">Gebruikersbeheer</div>
                <div class="admin-card__desc">
                    Gebruikers beheren en rollen aanpassen (admin / user).
                </div>
            </a>

            <a href="/admin/inbox" class="admin-card">
                <div class="admin-card__title">Contact inbox</div>
                <div class="admin-card__desc">
                    Bekijk en beheer binnengekomen contactberichten.
                </div>
            </a>

        </div>

        <!-- INFO PANEL -->
        <div class="admin-panel" style="margin-top:24px">
            <h3 class="admin-panel__title">Informatie</h3>
            <p class="muted">
                Dit adminpaneel is uitsluitend toegankelijk voor beheerders.
                Wijzigingen worden direct zichtbaar op de website.
            </p>
        </div>

    </main>

</div>

</section>
HTML;
    }
}
