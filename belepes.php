<?php
// belepes.php
require __DIR__ . '/auth.php';

$hiba = null;
$email = '';
$zarolva = ($_SESSION['hibas_probak'] ?? 0) >= 5 && time() - ($_SESSION['utolso_hiba'] ?? 0) < 300;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($zarolva) {
        http_response_code(429);
        header('Retry-After: 300');
        exit('Túl sok sikertelen próbálkozás. Próbáld újra 5 perc múlva.');
    }
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $stmt = db()->prepare('SELECT id, jelszo_hash FROM felhasznalo WHERE email = ?');
    $stmt->execute([$email]);
    $f = $stmt->fetch();

    // időzítés kiegyenlítése: nem létező fióknál is futtatunk egy verify-t
    $ok = password_verify((string)($_POST['jelszo'] ?? ''), $f['jelszo_hash'] ?? '$2y$12$abcdefghijklmnopqrstuuJ1kZ1k0p6Wm0o5mJ6m3t3M4o6m1hW.');

    if ($f && $ok) {
        session_regenerate_id(true); // munkamenet-fixáció ellen
        $_SESSION['uid'] = (int)$f['id'];
        unset($_SESSION['hibas_probak'], $_SESSION['utolso_hiba']);

        if (password_needs_rehash($f['jelszo_hash'], PASSWORD_DEFAULT)) {
            db()->prepare('UPDATE felhasznalo SET jelszo_hash = ? WHERE id = ?')
                ->execute([password_hash((string)$_POST['jelszo'], PASSWORD_DEFAULT), $f['id']]);
        }
        header('Location: /profil.php', true, 303);
        exit;
    }

    $_SESSION['hibas_probak'] = ($_SESSION['hibas_probak'] ?? 0) + 1;
    $_SESSION['utolso_hiba'] = time();
    $hiba = 'Hibás e-mail vagy jelszó.';
    http_response_code(401);
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <title>Bejelentkezés</title>
</head>
<body>
<h1>Bejelentkezés</h1>

<?php if (!empty($_SESSION['flash'])): ?>
  <p class="ok" style="color: green;"><?= e($_SESSION['flash']) ?></p>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if ($hiba): ?>
  <p class="hiba" style="color: red;"><?= e($hiba) ?></p>
<?php endif; ?>

<form method="post" action="/belepes.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  
  <div>
    <label for="email">E-mail</label><br>
    <input id="email" name="email" type="email" autocomplete="username" value="<?= e($email) ?>" required>
  </div>
  
  <div>
    <label for="jelszo">Jelszó</label><br>
    <input id="jelszo" name="jelszo" type="password" autocomplete="current-password" required>
  </div>
  
  <br>
  <button type="submit">Belépés</button>
</form>

<p><a href="/regisztracio.php">Még nincs fiókod? Regisztráció</a></p>
</body>
</html>