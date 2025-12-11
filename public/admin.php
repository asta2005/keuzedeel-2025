<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;

// Beschikbare secties in het beheerpaneel
$sections = [
    'pages' => [
        'label' => "Pagina's",
        'model' => Page::class,
        'help'  => 'Gebruik deze pagina voor statische inhoud zoals Werken bij, Contact, Expertise en Projectmanagement.'
    ],
    'projects' => [
        'label' => 'Opdrachten & projecten',
        'model' => Project::class,
        'help'  => 'Hier beheer je de projecten die op de projectenpagina als kaarten worden getoond.'
    ],
    'publications' => [
        'label' => 'Publicaties',
        'model' => Publication::class,
        'help'  => 'Beheer hier rapporten, documenten en overige publicaties.'
    ],
];

// Bepaal actieve sectie
$activeSection = $_GET['section'] ?? ($_POST['section'] ?? 'pages');
if (!isset($sections[$activeSection])) {
    $activeSection = 'pages';
}

// Verwerking van formulier (opslaan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelClass = $sections[$activeSection]['model'];
    $id   = $_POST['id'] ?? null;

    $data = [
        'title'   => trim($_POST['title'] ?? ''),
        'slug'    => trim($_POST['slug'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'img'     => trim($_POST['img'] ?? ''),
    ];

    if ($id) {
        $item = $modelClass::find($id);
        if ($item) {
            $item->update($data);
        }
    } else {
        $modelClass::create($data);
    }

    header('Location: admin.php?section=' . urlencode($activeSection));
    exit;
}

// Verwijderen
$modelClass = $sections[$activeSection]['model'];
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id) {
        $modelClass::destroy($id);
    }
    header('Location: admin.php?section=' . urlencode($activeSection));
    exit;
}

// Ophalen voor overzicht en eventuele bewerking
$items = $modelClass::orderBy('created_at', 'desc')->get();
$editItem = null;
if (isset($_GET['edit'])) {
    $editItem = $modelClass::find((int) $_GET['edit']);
}

// Kleine hulpfunctie voor veilige output
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$currentSectionMeta = $sections[$activeSection];
?><!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Beheer - PMB Amsterdam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .admin-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 40px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .admin-header h1 {
            margin: 0;
        }
        .admin-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .admin-nav a {
            padding: 8px 14px;
            border-radius: 20px;
            text-decoration: none;
            border: 1px solid #ccc;
            font-size: 0.9rem;
            background-color: #fff;
        }
        .admin-nav a.active {
            background-color: #e30613;
            border-color: #e30613;
            color: #fff;
        }
        .admin-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            gap: 20px;
        }
        @media (max-width: 900px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
        }
        .admin-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .admin-card h2 {
            margin-top: 0;
        }
        table.admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        table.admin-table th,
        table.admin-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        table.admin-table th {
            text-align: left;
            font-weight: bold;
        }
        table.admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-actions a {
            margin-right: 8px;
            font-size: 0.85rem;
        }
        .admin-tag {
            display: inline-block;
            padding: 2px 8px;
            background-color: #f1f1f1;
            border-radius: 999px;
            font-size: 0.75rem;
            color: #555;
        }
        .admin-help {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 10px;
        }
        .form-row {
            margin-bottom: 10px;
        }
        .form-row label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .form-row input[type="text"],
        .form-row textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .form-row textarea {
            min-height: 180px;
        }
        .btn {
            display: inline-block;
            border-radius: 20px;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .btn-primary {
            background-color: #e30613;
            color: #fff;
        }
        .btn-secondary {
            background-color: #f1f1f1;
            color: #333;
        }
        .topbar {
            position: static;
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="header-container">
        <div class="logo">
            <a href="/"><img src="/img/logo.png" alt="PMB Amsterdam Logo"></a>
        </div>
        <nav class="main-nav">
            <ul>
                <li><span class="admin-tag">Beheeromgeving</span></li>
                <li><a href="/">Terug naar website</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="admin-wrapper">
    <div class="admin-header">
        <h1>Beheer PMB Amsterdam</h1>
    </div>

    <nav class="admin-nav">
        <?php foreach ($sections as $key => $meta): ?>
            <a href="admin.php?section=<?= e($key) ?>" class="<?= $key === $activeSection ? 'active' : '' ?>">
                <?= e($meta['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-layout">
        <section class="admin-card">
            <h2>Overzicht – <?= e($currentSectionMeta['label']) ?></h2>
            <p class="admin-help"><?= e($currentSectionMeta['help']) ?></p>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Titel</th>
                    <th>Slug</th>
                    <th>Afbeelding</th>
                    <th>Acties</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item->title ?? '') ?></td>
                        <td><code><?= e($item->slug ?? '') ?></code></td>
                        <td>
                            <?php if (!empty($item->img)): ?>
                                <span class="admin-tag">ja</span>
                            <?php else: ?>
                                <span class="admin-tag">geen</span>
                            <?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <a href="admin.php?section=<?= e($activeSection) ?>&edit=<?= (int) $item->id ?>">Bewerken</a>
                            <a href="admin.php?section=<?= e($activeSection) ?>&delete=<?= (int) $item->id ?>" onclick="return confirm('Weet je zeker dat je dit item wilt verwijderen?');">Verwijderen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($items) === 0): ?>
                    <tr>
                        <td colspan="4"><em>Er zijn nog geen items in deze sectie.</em></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="admin-card">
            <h2><?= $editItem ? 'Bewerk item' : 'Nieuw item' ?></h2>
            <form method="post">
                <input type="hidden" name="section" value="<?= e($activeSection) ?>">
                <input type="hidden" name="id" value="<?= $editItem ? (int) $editItem->id : '' ?>">

                <div class="form-row">
                    <label for="title">Titel</label>
                    <input type="text" id="title" name="title" value="<?= $editItem ? e($editItem->title ?? '') : '' ?>" required>
                </div>

                <div class="form-row">
                    <label for="slug">Slug (url-deel, bijv. <code>werken-bij</code>)</label>
                    <input type="text" id="slug" name="slug" value="<?= $editItem ? e($editItem->slug ?? '') : '' ?>" required>
                </div>

                <div class="form-row">
                    <label for="img">Afbeelding (optionele URL voor header/kaart)</label>
                    <input type="text" id="img" name="img" value="<?= $editItem ? e($editItem->img ?? '') : '' ?>">
                </div>

                <div class="form-row">
                    <label for="content">Inhoud (HTML toegestaan)</label>
                    <textarea id="content" name="content"><?= $editItem ? e($editItem->content ?? '') : '' ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Opslaan</button>
                <?php if ($editItem): ?>
                    <a href="admin.php?section=<?= e($activeSection) ?>" class="btn btn-secondary">Nieuwe toevoegen</a>
                <?php endif; ?>
            </form>
        </section>
    </div>
</main>
</body>
</html>
