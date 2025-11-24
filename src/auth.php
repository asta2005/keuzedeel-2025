<?php
namespace App;
session_start();

class Auth {
    public static function check() {
        return !empty($_SESSION['admin_logged']);
    }
    public static function require() {
        if (!self::check()) {
            header('Location: /?page=admin_login');
            exit;
        }
    }
    public static function attempt($user, $pass) {
        $u = getenv('ADMIN_USER') ?: 'admin';
        $p = getenv('ADMIN_PASS') ?: 'admin123';
        if ($user === $u && $pass === $p) {
            $_SESSION['admin_logged'] = true;
            return true;
        }
        return false;
    }
    public static function logout() {
        session_destroy();
    }
}
