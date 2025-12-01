<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ProjectController {
    private $pdo;
    private $view;
    private $uploadDir;

    public function __construct(\PDO $pdo, Twig $view, string $uploadDir) {
        $this->pdo = $pdo;
        $this->view = $view;
        $this->uploadDir = $uploadDir;
    }

    public function list(Request $request, Response $response) {
        $stmt = $this->pdo->query('SELECT * FROM projects ORDER BY created_at DESC');
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->view->render($response, 'projects.twig', ['projects' => $projects]);
    }

    public function single(Request $request, Response $response, $args) {
        $id = (int)$args['id'];
        $stmt = $this->pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$project) {
            $response->getBody()->write('Project niet gevonden');
            return $response->withStatus(404);
        }
        return $this->view->render($response, 'project_single.twig', ['project' => $project]);
    }

    public function adminList(Request $request, Response $response) {
        $stmt = $this->pdo->query('SELECT * FROM projects ORDER BY created_at DESC');
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->view->render($response, 'admin_dashboard.twig', ['projects' => $projects]);
    }

    public function showAdd(Request $request, Response $response) {
        return $this->view->render($response, 'add_project.twig', []);
    }

    public function handleAdd(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $title = trim($data['title'] ?? '');
        $municipality = trim($data['municipality'] ?? '');
        $description = trim($data['description'] ?? '');

        // handle upload
        $uploadedFiles = $request->getUploadedFiles();
        $imageName = null;
        if (isset($uploadedFiles['image'])) {
            $up = $uploadedFiles['image'];
            if ($up->getError() === UPLOAD_ERR_OK) {
                $basename = bin2hex(random_bytes(8)) . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '', $up->getClientFilename());
                $target = $this->uploadDir . DIRECTORY_SEPARATOR . $basename;
                $up->moveTo($target);
                $imageName = 'uploads/' . $basename;
            }
        }

        $stmt = $this->pdo->prepare('INSERT INTO projects (title, municipality, description, image) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $municipality, $description, $imageName]);
        return $response->withHeader('Location', '/admin')->withStatus(302);
    }
}
