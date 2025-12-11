<?php
namespace App\Controllers;

use App\Models\Application;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ApplicationController {

    public function form(): string {
        return <<<HTML
<section class="page-wrapper">
<h1>Solliciteren</h1>

<form method="post" action="/solliciteer" enctype="multipart/form-data">
    <label>Naam</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Telefoonnummer</label>
    <input type="text" name="phone" required>

    <label>Motivatie</label>
    <textarea name="motivation" required></textarea>

    <label>Upload CV (PDF)</label>
    <input type="file" name="cv" accept="application/pdf" required>

    <button class="btn-primary">Versturen</button>
</form>
</section>
HTML;
    }

    public function submit(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $cv = $files['cv'];
        $filename = time() . "_" . $cv->getClientFilename();
        $cv->moveTo(__DIR__ . "/../../public/uploads/" . $filename);

        Application::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'motivation' => $data['motivation'],
            'cv_file' => $filename,
        ]);

        $response->getBody()->write("<section class='page-wrapper'><h1>Bedankt!</h1></section>");
        return $response;
    }
}
