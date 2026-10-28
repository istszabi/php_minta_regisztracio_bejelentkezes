<?php
// auth.php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0, 
    'path' => '/', 
    'httponly' => true,
    'samesite' => 'Lax', 
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

function db(): PDO {
    static $pdo = null;
    $pdo ??= new PDO('mysql:host=localhost;dbname=umfst;charset=utf8mb4', 'app', 'titok', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(string $s): string { 
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}

function csrf_token(): string { 
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); 
}

function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { 
        http_response_code(403); 
        exit('Érvénytelen kérés.'); 
    }
}

function bejelentkezve_kell(): array {
    if (empty($_SESSION['uid'])) {
        header('Location: /belepes.php', true, 302);
        exit;
    }
    $stmt = db()->prepare('SELECT id, nev, email, letrehozva FROM felhasznalo WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    return $stmt->fetch() ?: (function (): never { 
        $_SESSION = []; 
        header('Location: /belepes.php', true, 302); 
        exit; 
    })();
}