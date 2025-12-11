<?php
namespace App\Controllers;

use App\Models\ContactMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContactFormController {

    public function show(): string {
        return <<<HTML
<style>

.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-top: 20px;
}

.contact-card {
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.contact-card h2 {
    margin-top: 0;
}

.contact-form label {
    font-weight: bold;
    margin-top: 10px;
    display: block;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #bbb;
    margin-bottom: 15px;
    font-size: 1rem;
}

.contact-form textarea {
    min-height: 140px;
}

.contact-form button {
    background: #e30613;
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}

.contact-form button:hover {
    background: #b2050f;
}

</style>

<section class="page-wrapper">
<h1>Contact</h1>

<div class="contact-container">

    <div class="contact-card">
        <h2>Neem contact op</h2>

        <form method="post" action="/contact" class="contact-form">

            <label>Naam</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Onderwerp</label>
            <input type="text" name="subject" required>

            <label>Bericht</label>
            <textarea name="message" required></textarea>

            <button>Versturen</button>

        </form>
    </div>

    <div class="contact-card">
        <h2>Contactgegevens</h2>

        <p><strong>PMB Amsterdam</strong><br>
        Gemeente Amsterdam</p>

        <p><strong>Email:</strong><br>
        info@pmb-amsterdam.nl</p>

        <p><strong>Adres:</strong><br>
        Amstel 1, 1011 PN Amsterdam</p>

        <p><strong>Telefoon:</strong><br>
        020 - 123 4567</p>
    </div>

</div>
</section>
HTML;
    }

    public function submit(Request $request, Response $response): Response {
        ContactMessage::create($request->getParsedBody());

        $response->getBody()->write("
            <section class='page-wrapper'>
                <h1>Bedankt!</h1>
                <p>Uw bericht is succesvol verzonden. Wij nemen zo snel mogelijk contact met u op.</p>
            </section>
        ");
        return $response;
    }
}
