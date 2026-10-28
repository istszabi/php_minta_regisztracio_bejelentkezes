<?php
// kilepes.php
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Allow: POST'); 
    http_response_code(405); 
    exit; 
}

csrf_check();

$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600, 
    'path' => $p['path'], 
    'httponly' => true, 
    'samesite' => 'Lax'
]);
session_destroy();

header('Location: /belepes.php', true, 303);
exit;