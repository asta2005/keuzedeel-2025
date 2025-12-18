<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ContactMessage;

class ContactFormController
{
    public function show(): string
    {
        return <<<HTML
<section class="page-wrapper">

  <div class="page-header">
    <h1>Contact</h1>
    <p class="muted">
      Heeft u een vraag of wilt u contact opnemen met PMB Amsterdam?
      Vul het formulier in en wij nemen zo spoedig mogelijk contact met u op.
    </p>
  </div>

  <div class="contact-grid">

    <div class="contact-info">
      <h3>PMB Amsterdam</h3>
      <p>Projectmanagementbureau<br>Gemeente Amsterdam</p>

      <p><strong>Adres</strong><br>
        Weesperstraat 113<br>
        1018 VN Amsterdam
      </p>

      <p><strong>E-mail</strong><br>pmb@amsterdam.nl</p>
      <p><strong>Telefoon</strong><br>14 020</p>
    </div>

    <div class="contact-form-card">
      <form method="post" action="/contact" class="form">

        <label>Naam</label>
        <input name="name" required>

        <label>E-mailadres</label>
        <input type="email" name="email" required>

        <label>Onderwerp</label>
        <input name="subject" required>

        <label>Bericht</label>
        <textarea name="message" rows="5" required></textarea>

        <button class="btn btn--primary" type="submit">
          Versturen
        </button>

      </form>
    </div>

  </div>

</section>
HTML;
    }

    public function submit(Request $request): string
    {
        $data = $request->getParsedBody();

        ContactMessage::create([
            'NAME'    => trim($data['name']),
            'email'   => trim($data['email']),
            'SUBJECT' => trim($data['subject']),
            'message' => trim($data['message']),
        ]);

        return <<<HTML
<section class="page-wrapper">
  <div class="admin-panel">
    <h2>Bedankt!</h2>
    <p>Uw bericht is succesvol verzonden.</p>
    <a href="/" class="btn btn--primary">Terug naar home</a>
  </div>
</section>
HTML;
    }
}
