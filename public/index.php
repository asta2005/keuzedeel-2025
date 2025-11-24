<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';

use App\DB;
use App\Auth;

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    //'cache' => __DIR__ . '/../cache'
]);

$page = $_GET['page'] ?? 'home';

// helper for flash messages
function flash($msg=null) {
    if ($msg===null) {
        if(!empty($_SESSION['flash'])) { $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; }
        return null;
    } else {
        $_SESSION['flash'] = $msg;
    }
}

session_start();

try {
switch ($page) {
    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = DB::get();
            $stmt = $db->prepare('INSERT INTO contacts (name,email,message,created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['message'] ?? '']);
            $_SESSION['flash'] = 'Bedankt, uw bericht is verzonden.';
            header('Location: /?page=contact');
            exit;
        }
        echo $twig->render('contact.twig', ['flash'=>flash()]);
        break;

    case 'vacancies':
        $db = DB::get();
        $stmt = $db->query('SELECT id,title,summary FROM vacancies ORDER BY created_at DESC');
        $vacancies = $stmt->fetchAll();
        echo $twig->render('vacancies.twig', ['vacancies'=>$vacancies]);
        break;

    case 'vacancy':
        $id = intval($_GET['id'] ?? 0);
        $db = DB::get();
        $stmt = $db->prepare('SELECT * FROM vacancies WHERE id = ?');
        $stmt->execute([$id]);
        $vac = $stmt->fetch();
        if (!$vac) { http_response_code(404); echo $twig->render('404.twig'); break; }
        echo $twig->render('vacancy.twig', ['vac'=>$vac, 'flash'=>flash()]);
        break;

    case 'apply':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = DB::get();
            $vac_id = intval($_POST['vacancy_id'] ?? 0);
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            // handle upload
            $cv_path = null;
            if (!empty($_FILES['cv']['tmp_name'])) {
                $updir = __DIR__ . '/../uploads';
                if (!is_dir($updir)) mkdir($updir, 0755, true);
                $fname = time() . '_' . basename($_FILES['cv']['name']);
                $target = $updir . '/' . $fname;
                move_uploaded_file($_FILES['cv']['tmp_name'], $target);
                $cv_path = 'uploads/' . $fname;
            }
            $stmt = $db->prepare('INSERT INTO applications (vacancy_id,name,email,cv_path,created_at) VALUES (?,?,?,?,NOW())');
            $stmt->execute([$vac_id,$name,$email,$cv_path]);
            $_SESSION['flash'] = 'Uw sollicitatie is ontvangen. Dank!';
            header('Location: /?page=vacancy&id=' . $vac_id);
            exit;
        }
        header('Location: /');
        break;

    case 'admin_login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $_POST['user'] ?? '';
            $pass = $_POST['pass'] ?? '';
            if (Auth::attempt($user,$pass)) {
                header('Location: /?page=admin');
                exit;
            } else {
                echo $twig->render('admin/login.twig', ['error'=>'Onjuiste gegevens']);
                break;
            }
        }
        echo $twig->render('admin/login.twig', []);
        break;

    case 'admin_logout':
        Auth::logout();
        header('Location: /');
        break;

    case 'admin':
        Auth::require();
        $db = DB::get();
        $cntV = $db->query('SELECT COUNT(*) AS c FROM vacancies')->fetchColumn();
        $cntC = $db->query('SELECT COUNT(*) AS c FROM contacts')->fetchColumn();
        echo $twig->render('admin/dashboard.twig', ['cntV'=>$cntV,'cntC'=>$cntC]);
        break;

    case 'admin_vacancies':
        Auth::require();
        $db = DB::get();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // create
            $stmt = $db->prepare('INSERT INTO vacancies (title,summary,description,created_at) VALUES (?,?,?,NOW())');
            $stmt->execute([$_POST['title'], $_POST['summary'], $_POST['description']]);
            header('Location: /?page=admin_vacancies');
            exit;
        }
        $vac = $db->query('SELECT * FROM vacancies ORDER BY created_at DESC')->fetchAll();
        echo $twig->render('admin/vacancies.twig', ['vacancies'=>$vac]);
        break;

    case 'admin_contacts':
        Auth::require();
        $db = DB::get();
        $contacts = $db->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll();
        echo $twig->render('admin/contacts.twig', ['contacts'=>$contacts]);
        break;

    default:
        echo $twig->render('home.twig', []);
}
} catch (Exception $e) {
    http_response_code(500);
    echo "Fout: " . htmlspecialchars($e->getMessage());
}
